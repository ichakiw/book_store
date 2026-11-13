<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderUserController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $query = Order::with('items')
            ->where('user_id', Auth::id())
            ->latest();

        if ($status && in_array($status, [
            'menunggu_pembayaran',
            'menunggu_konfirmasi',
            'diproses',
            'dikirim',
            'selesai',
            'dibatalkan',
            'pembayaran_ditolak',
        ])) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(8)->withQueryString();

        $statusCounts = [
            'all' => Order::where('user_id', Auth::id())->count(),
            'menunggu_pembayaran' => Order::where('user_id', Auth::id())->where('status', 'menunggu_pembayaran')->count(),
            'menunggu_konfirmasi' => Order::where('user_id', Auth::id())->where('status', 'menunggu_konfirmasi')->count(),
            'diproses' => Order::where('user_id', Auth::id())->where('status', 'diproses')->count(),
            'dikirim' => Order::where('user_id', Auth::id())->where('status', 'dikirim')->count(),
            'selesai' => Order::where('user_id', Auth::id())->where('status', 'selesai')->count(),
        ];

        return view('store.orders.index', compact('orders', 'statusCounts', 'status'));
    }

    public function show(string $orderNumber): View|RedirectResponse
    {
        $order = Order::with(['items.book'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (Auth::user()?->isAdmin()) {
            return redirect()->route('admin.orders.show', $order->id);
        }

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pesanan ini.');
        }

        return view('store.orders.show', compact('order'));
    }

    public function payment(string $orderNumber): View|RedirectResponse
    {
        $order = Order::with('items')
            ->where('user_id', Auth::id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Bank details
        $bankAccounts = [
            [
                'bank' => 'Bank Central Asia (BCA)',
                'number' => '8820 1928 331',
                'name' => 'PT BOOK STORE INDONESIA',
                'code' => '014',
            ],
            [
                'bank' => 'Bank Mandiri',
                'number' => '137 00 2938 1029',
                'name' => 'PT BOOK STORE INDONESIA',
                'code' => '008',
            ],
            [
                'bank' => 'Bank Rakyat Indonesia (BRI)',
                'number' => '0341 01 002938 501',
                'name' => 'PT BOOK STORE INDONESIA',
                'code' => '002',
            ],
        ];

        return view('store.orders.payment', compact('order', 'bankAccounts'));
    }

    public function uploadPaymentProof(Request $request, string $orderNumber): RedirectResponse
    {
        $order = Order::where('user_id', Auth::id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! in_array($order->status, ['menunggu_pembayaran', 'pembayaran_ditolak'])) {
            return back()->with('error', 'Status pesanan tidak memungkinkan untuk upload bukti pembayaran.');
        }

        $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ], [
            'payment_proof.required' => 'Pilih file bukti transfer terlebih dahulu.',
            'payment_proof.image' => 'File harus berupa gambar.',
            'payment_proof.mimes' => 'Format file yang diperbolehkan: jpeg, png, jpg, webp.',
            'payment_proof.max' => 'Ukuran file maksimal 4MB.',
        ]);

        if ($request->hasFile('payment_proof')) {
            // Delete old file if exists
            if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            $path = $request->file('payment_proof')->store('proofs', 'public');

            $order->update([
                'payment_proof' => $path,
                'status' => 'menunggu_konfirmasi',
                'paid_at' => now(),
                'payment_note' => null, // Reset previous rejection note if any
            ]);
        }

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', 'Bukti pembayaran berhasil diupload! Menunggu verifikasi dari Admin.');
    }

    public function cancel(string $orderNumber): RedirectResponse
    {
        $order = Order::where('user_id', Auth::id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan pada tahap ini.');
        }

        $order->update([
            'status' => 'dibatalkan',
        ]);

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function confirmReceipt(string $orderNumber): RedirectResponse
    {
        $order = Order::where('user_id', Auth::id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->status !== 'dikirim') {
            return back()->with('error', 'Pesanan belum dalam status dikirim.');
        }

        $order->update([
            'status' => 'selesai',
            'completed_at' => now(),
        ]);

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', 'Terima kasih telah berbelanja! Pesanan Anda telah ditandai selesai.');
    }

    public function invoice(string $orderNumber): View
    {
        $order = Order::with(['items.book', 'user'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Check permission if authenticated: admin can view any invoice, regular user only their own
        if (Auth::check() && ! Auth::user()->isAdmin() && $order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat invoice pesanan ini.');
        }

        return view('store.orders.invoice', compact('order'));
    }
}
