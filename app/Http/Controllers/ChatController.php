<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        // Mark unread messages sent by admin as read
        ChatMessage::where('user_id', $userId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::with('sender')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('store.chat.index', compact('messages'));
    }

    public function getMessages(): JsonResponse
    {
        $userId = Auth::id();

        // Mark incoming unread admin messages as read
        ChatMessage::where('user_id', $userId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::with('sender')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($userId) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_me' => $msg->sender_id === $userId,
                    'sender_name' => $msg->sender?->name ?? 'User',
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->format('d/m/Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max' => 'Pesan maksimal 2000 karakter.',
        ]);

        $userId = Auth::id();

        $message = ChatMessage::create([
            'user_id' => $userId,
            'sender_id' => $userId,
            'message' => trim($request->input('message')),
            'is_read' => false,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_me' => true,
                    'sender_name' => Auth::user()->name,
                    'time' => $message->created_at->format('H:i'),
                    'date' => $message->created_at->format('d/m/Y'),
                ],
            ]);
        }

        return redirect()->route('chat.index');
    }
}
