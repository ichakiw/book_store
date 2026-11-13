<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount(['books' => fn($q) => $q->where('is_active', true)])->get();

        $query = Book::with('category')->where('is_active', true);

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Stock availability filter
        if ($request->input('in_stock') === '1') {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sort = $request->input('sort', 'terbaru');
        match ($sort) {
            'harga_asc' => $query->orderBy('price', 'asc'),
            'harga_desc' => $query->orderBy('price', 'desc'),
            'judul_asc' => $query->orderBy('title', 'asc'),
            'stok' => $query->orderBy('stock', 'desc'),
            default => $query->latest(),
        };

        $books = $query->paginate(12)->withQueryString();

        $selectedCategory = $categorySlug
            ? Category::where('slug', $categorySlug)->first()
            : null;

        return view('store.catalog', compact('books', 'categories', 'selectedCategory'));
    }

    public function show(string $slug): View
    {
        $book = Book::with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedBooks = Book::with('category')
            ->where('is_active', true)
            ->where('id', '!=', $book->id)
            ->where('category_id', $book->category_id)
            ->take(4)
            ->get();

        return view('store.detail', compact('book', 'relatedBooks'));
    }
}
