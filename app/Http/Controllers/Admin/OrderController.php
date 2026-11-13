<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'items.book']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sort = $request->input('sort', 'terbaru');
        match ($sort) {
            'terlama' => $query->oldest(),
            'nominal_desc' => $query->orderBy('grand_total', 'desc'),
            'nominal_asc' => $query->orderBy('grand_total', 'asc'),
            default => $query->latest(),
        };

        $orders = $query->paginate(10)->withQueryString();

        $statusCounts = [
            'all' => Order::count(),
            'menunggu_pembayaran' => Order::where('status', 'menunggu_pembayaran')->count(),
            'menunggu_konfirmasi' => Order::where('status', 'menunggu_konfirmasi')->count(),
            'diproses' => Order::where('status', 'diproses')->count(),
            'dikirim' => Order::where('status', 'dikirim')->count(),
            'selesai' => Order::where('status', 'selesai')->count(),
            'pembayaran_ditolak' => Order::where('status', 'pembayaran_ditolak')->count(),
            'dibatalkan' => Order::where('status', 'dibatalkan')->count(),
        ];

        $kpis = [
            'total_orders' => $statusCounts['all'],
            'pending_confirmation' => $statusCounts['menunggu_konfirmasi'],
            'processing' => $statusCounts['diproses'],
            'total_revenue' => Order::where('status', 'selesai')->sum('grand_total'),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts', 'status', 'kpis', 'sort'));
    }

    public function show(int $id): View
    {
        $order = Order::with(['user', 'items.book'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function confirmPayment(Request $request, int $id): RedirectResponse
    {
        $order = Order::with('items.book')->findOrFail($id);

        if (!in_array($order->status, ['menunggu_konfirmasi', 'menunggu_pembayaran'])) {
            return back()->with('error', 'Status pesanan saat ini tidak dapat dikonfirmasi pembayarannya.');
        }

        // Check stock availability if not yet reduced
        if (!$order->stock_reduced) {
            foreach ($order->items as $item) {
                if ($item->book && $item->book->stock < $item->quantity) {
                    return back()->with('error', "Gagal konfirmasi: Stok buku \"{$item->book->title}\" tidak mencukupi (Tersisa: {$item->book->stock}, Dibutuhkan: {$item->quantity}).");
                }
            }
        }

        DB::transaction(function () use ($order) {
            // Decrement book stock safely
            if (!$order->stock_reduced) {
                foreach ($order->items as $item) {
                    if ($item->book) {
                        $item->book->decrement('stock', $item->quantity);
                    }
                }
                $order->stock_reduced = true;
            }

            $order->update([
                'status' => 'diproses',
                'payment_note' => null,
            ]);
        });

        return back()->with('success', "Pembayaran untuk pesanan {$order->order_number} berhasil dikonfirmasi! Stok buku telah otomatis berkurang dan pesanan siap diproses.");
    }

    public function rejectPayment(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Alasan penolakan pembayaran wajib diisi.',
        ]);

        $order->update([
            'status' => 'pembayaran_ditolak',
            'payment_note' => $request->input('reason'),
        ]);

        return back()->with('warning', "Pembayaran untuk pesanan {$order->order_number} telah ditolak. Alasan telah dikirimkan ke pelanggan.");
    }

    public function updateShipping(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'courier_name' => ['required', 'string', 'max:100'],
            'tracking_number' => ['required', 'string', 'max:100'],
        ], [
            'courier_name.required' => 'Nama kurir / ekspedisi pengiriman wajib diisi.',
            'tracking_number.required' => 'Nomor resi pengiriman wajib diisi.',
        ]);

        $order->update([
            'courier_name' => $request->input('courier_name'),
            'tracking_number' => $request->input('tracking_number'),
            'status' => 'dikirim',
            'shipped_at' => now(),
        ]);

        return back()->with('success', "Pengiriman untuk pesanan {$order->order_number} berhasil diperbarui dengan no. resi {$order->tracking_number}. Status diubah menjadi Dikirim.");
    }

    public function completeOrder(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $order->update([
            'status' => 'selesai',
            'completed_at' => now(),
        ]);

        return back()->with('success', "Pesanan {$order->order_number} telah ditandai Selesai.");
    }

    public function cancelOrder(Request $request, int $id): RedirectResponse
    {
        $order = Order::with('items.book')->findOrFail($id);

        if ($order->status === 'selesai') {
            return back()->with('error', 'Pesanan yang sudah selesai tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($order) {
            // Restore stock if it was already decremented
            if ($order->stock_reduced) {
                foreach ($order->items as $item) {
                    if ($item->book) {
                        $item->book->increment('stock', $item->quantity);
                    }
                }
                $order->stock_reduced = false;
            }

            $order->update([
                'status' => 'dibatalkan',
            ]);
        });

        return back()->with('success', "Pesanan {$order->order_number} berhasil dibatalkan dan stok buku telah dikembalikan.");
    }
}
