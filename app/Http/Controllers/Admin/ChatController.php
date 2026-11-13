<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        // Get all users who have chat messages or customers
        $userIdsWithChats = ChatMessage::select('user_id')
            ->distinct()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIdsWithChats)
            ->where('role', '!=', 'admin')
            ->get()
            ->map(function ($u) {
                $lastMsg = ChatMessage::where('user_id', $u->id)
                    ->latest()
                    ->first();

                $unreadCount = ChatMessage::where('user_id', $u->id)
                    ->where('sender_id', $u->id)
                    ->where('is_read', false)
                    ->count();

                $u->last_message = $lastMsg;
                $u->unread_count = $unreadCount;

                return $u;
            })
            ->sortByDesc(fn ($u) => $u->last_message?->created_at);

        // Also fetch other customers who may not have chatted yet in case admin wants to initiate
        $allCustomers = User::where('role', 'user')->where('status', 'active')->orderBy('name')->get();

        $selectedUserId = $request->input('user_id') ? (int) $request->input('user_id') : ($users->first()?->id ?? $allCustomers->first()?->id ?? null);
        $selectedUser = $selectedUserId ? User::find($selectedUserId) : null;

        $messages = collect();
        if ($selectedUser) {
            // Mark customer's messages as read by admin
            ChatMessage::where('user_id', $selectedUser->id)
                ->where('sender_id', $selectedUser->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = ChatMessage::with('sender')
                ->where('user_id', $selectedUser->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('admin.chat.index', compact('users', 'allCustomers', 'selectedUser', 'messages'));
    }

    public function getMessages(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Mark incoming messages as read
        ChatMessage::where('user_id', $user->id)
            ->where('sender_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::with('sender')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_admin' => $msg->isFromAdmin(),
                    'sender_name' => $msg->sender?->name ?? 'Pengirim',
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->format('d/m/Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, int $userId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Pesan balasan tidak boleh kosong.',
            'message.max' => 'Pesan maksimal 2000 karakter.',
        ]);

        $user = User::findOrFail($userId);

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'sender_id' => Auth::id(),
            'message' => trim($request->input('message')),
            'is_read' => false,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_admin' => true,
                    'sender_name' => Auth::user()->name,
                    'time' => $message->created_at->format('H:i'),
                    'date' => $message->created_at->format('d/m/Y'),
                ],
            ]);
        }

        return redirect()->route('admin.chat.index', ['user_id' => $user->id]);
    }
}
