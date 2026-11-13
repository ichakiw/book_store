<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();
        $cartItems = Cart::with(['book.category'])
            ->where('user_id', $userId)
            ->get();

        // Calculate totals
        $subtotal = 0;
        $hasOutOfStock = false;

        foreach ($cartItems as $item) {
            if (!$item->book || !$item->book->is_active || $item->book->trashed()) {
                // Book was deleted or deactivated
                $item->out_of_stock = true;
                $hasOutOfStock = true;
            } elseif ($item->quantity > $item->book->stock) {
                $item->insufficient_stock = true;
                $hasOutOfStock = true;
                $subtotal += $item->subtotal;
            } else {
                $subtotal += $item->subtotal;
            }
        }

        $shippingCost = $cartItems->isNotEmpty() ? 15000 : 0;
        $grandTotal = $subtotal + $shippingCost;

        return view('store.cart', compact('cartItems', 'subtotal', 'shippingCost', 'grandTotal', 'hasOutOfStock'));
    }

    public function add(Request $request, int $bookId): RedirectResponse
    {
        $book = Book::where('is_active', true)->findOrFail($bookId);
        $qty = max(1, (int) $request->input('quantity', 1));

        if ($book->stock < 1) {
            return back()->with('error', 'Maaf, stok buku ini sedang habis.');
        }

        $userId = Auth::id();
        $cartItem = Cart::where('user_id', $userId)->where('book_id', $book->id)->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $qty;
            if ($newQty > $book->stock) {
                return back()->with('error', "Stok hanya tersedia {$book->stock} eks. Anda sudah memiliki {$cartItem->quantity} di keranjang.");
            }
            $cartItem->update(['quantity' => $newQty]);
        } else {
            if ($qty > $book->stock) {
                return back()->with('error', "Stok hanya tersedia {$book->stock} eks.");
            }
            Cart::create([
                'user_id' => $userId,
                'book_id' => $book->id,
                'quantity' => $qty,
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Buku "' . $book->title . '" berhasil ditambahkan ke keranjang belanja.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $cartItem = Cart::with('book')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $action = $request->input('action');
        $currentQty = $cartItem->quantity;

        if ($action === 'increase') {
            $newQty = $currentQty + 1;
        } elseif ($action === 'decrease') {
            $newQty = max(1, $currentQty - 1);
        } else {
            $newQty = max(1, (int) $request->input('quantity', 1));
        }

        if ($newQty > $cartItem->book->stock) {
            return back()->with('error', "Stok buku hanya tersisa {$cartItem->book->stock} eks.");
        }

        $cartItem->update(['quantity' => $newQty]);

        return back()->with('success', 'Jumlah buku berhasil diperbarui.');
    }

    public function remove(int $id): RedirectResponse
    {
        $cartItem = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('cart.index')
            ->with('success', 'Keranjang belanja berhasil dikosongkan.');
    }
}
