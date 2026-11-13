<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount(['books' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $featuredBooks = Book::with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        $bestsellerBooks = Book::with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('price', 'desc')
            ->take(4)
            ->get();

        $heroBooks = $featuredBooks->take(3);
        $totalBooks = Book::where('is_active', true)->count();

        return view('store.home', compact('categories', 'featuredBooks', 'bestsellerBooks', 'heroBooks', 'totalBooks'));
    }
}
