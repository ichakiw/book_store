<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $cartItems = Cart::with('book')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja Anda masih kosong.');
        }

        // Validate stock availability before showing checkout form
        foreach ($cartItems as $item) {
            if (!$item->book || !$item->book->is_active || $item->book->trashed()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Ada buku di keranjang yang sudah tidak tersedia. Silakan hapus sebelum melanjutkan.');
            }
            if ($item->quantity > $item->book->stock) {
                return redirect()->route('cart.index')
                    ->with('error', "Stok untuk \"{$item->book->title}\" tidak mencukupi (tersedia {$item->book->stock}).");
            }
        }

        $subtotal = $cartItems->sum(fn($i) => $i->subtotal);
        $shippingCost = 15000;
        $grandTotal = $subtotal + $shippingCost;

        return view('store.checkout', compact('user', 'cartItems', 'subtotal', 'shippingCost', 'grandTotal'));
    }

    public function process(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'recipient_phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'min:10', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'payment_method' => ['required', 'string', 'in:Transfer Bank BCA,Transfer Bank Mandiri,Transfer Bank BRI,QRIS'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_name.regex' => 'Nama penerima hanya boleh berisi huruf dan spasi.',
            'recipient_phone.required' => 'Nomor HP/WhatsApp penerima wajib diisi.',
            'recipient_phone.regex' => 'Nomor HP/WhatsApp penerima harus berupa angka.',
            'recipient_phone.min' => 'Nomor HP/WhatsApp penerima minimal 10 angka.',
            'shipping_address.required' => 'Alamat pengiriman lengkap wajib diisi.',
            'city.required' => 'Kota/Kabupaten tujuan wajib diisi.',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'payment_method.required' => 'Pilih salah satu metode pembayaran.',
        ]);

        $cartItems = Cart::with('book')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja Anda kosong.');
        }

        // Check stock availability
        foreach ($cartItems as $item) {
            if (!$item->book || !$item->book->is_active || $item->book->trashed()) {
                return redirect()->route('cart.index')
                    ->with('error', "Buku tidak ditemukan atau sudah dihapus.");
            }
            if ($item->quantity > $item->book->stock) {
                return redirect()->route('cart.index')
                    ->with('error', "Stok buku \"{$item->book->title}\" tidak mencukupi. Tersedia: {$item->book->stock}.");
            }
        }

        $subtotal = $cartItems->sum(fn($i) => $i->subtotal);
        $shippingCost = 15000;
        $grandTotal = $subtotal + $shippingCost;

        $order = DB::transaction(function () use ($user, $validated, $cartItems, $subtotal, $shippingCost, $grandTotal) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'total_amount' => $subtotal,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'status' => 'menunggu_pembayaran',
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'shipping_address' => $validated['shipping_address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'stock_reduced' => false,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item->book_id,
                    'book_title' => $item->book->title,
                    'book_author' => $item->book->author,
                    'book_cover' => $item->book->cover_image,
                    'price' => $item->book->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Empty cart
            Cart::where('user_id', $user->id)->delete();

            return $order;
        });

        return redirect()->route('orders.payment', $order->order_number)
            ->with('success', 'Pesanan Anda berhasil dibuat! Silakan selesaikan pembayaran.');
    }
}
