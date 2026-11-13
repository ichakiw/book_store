<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'user')
            ->withCount('orders')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $customers = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'user')->where('status', 'active')->count(),
            'blocked' => User::where('role', 'user')->where('status', 'blocked')->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function show(int $id): View
    {
        $customer = User::where('role', 'user')
            ->with(['orders' => fn($q) => $q->latest()])
            ->findOrFail($id);

        $totalSpent = $customer->orders->where('status', 'selesai')->sum('grand_total');

        return view('admin.customers.show', compact('customer', 'totalSpent'));
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $customer = User::where('role', 'user')->findOrFail($id);
        $newStatus = $customer->status === 'active' ? 'blocked' : 'active';
        $customer->update(['status' => $newStatus]);

        $statusStr = $newStatus === 'active' ? 'diaktifkan kembali' : 'diblokir / dinonaktifkan';
        return back()->with('success', "Status akun pelanggan {$customer->name} berhasil {$statusStr}.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $customer = User::where('role', 'user')->findOrFail($id);
        $name = $customer->name;
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', "Data pelanggan {$name} berhasil dihapus.");
    }
}
