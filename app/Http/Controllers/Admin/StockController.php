<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with('category')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $filter = $request->input('filter', 'all');
        if ($filter === 'low') {
            $query->where('stock', '<=', 5)->where('stock', '>', 0);
        } elseif ($filter === 'out') {
            $query->where('stock', '<=', 0);
        }

        $books = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        $stats = [
            'total' => Book::count(),
            'healthy' => Book::where('stock', '>', 5)->count(),
            'low' => Book::where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'out' => Book::where('stock', '<=', 0)->count(),
        ];

        return view('admin.stocks.index', compact('books', 'categories', 'stats', 'filter'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'stock_type' => ['required', 'in:set,add'],
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        $amount = (int) $request->input('amount');
        if ($request->input('stock_type') === 'add') {
            $book->increment('stock', $amount);
        } else {
            $book->update(['stock' => $amount]);
        }

        return back()->with('success', "Stok untuk \"{$book->title}\" berhasil diperbarui. Stok saat ini: {$book->fresh()->stock} eks.");
    }
}
