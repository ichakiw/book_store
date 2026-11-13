<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookController extends Controller
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

        if ($request->input('stock_status') === 'low') {
            $query->where('stock', '<=', 5)->where('stock', '>', 0);
        } elseif ($request->input('stock_status') === 'out') {
            $query->where('stock', '<=', 0);
        }

        $books = $query->paginate(10)->withQueryString();
        $categories = Category::all();

        return view('admin.books.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::all();

        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('books', 'title')->withoutTrashed(),
            ],
            'category_id' => ['nullable', 'exists:categories,id'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'isbn' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('books', 'isbn')->withoutTrashed(),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'published_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul buku wajib diisi.',
            'title.unique' => 'Buku dengan judul ini sudah terdaftar. Tidak boleh menambahkan buku dengan judul yang sama/duplikat.',
            'isbn.unique' => 'Nomor ISBN ini sudah digunakan oleh buku lain.',
            'author.required' => 'Nama penulis/pengarang wajib diisi.',
            'price.required' => 'Harga buku wajib diisi.',
            'stock.required' => 'Jumlah stok awal wajib diisi.',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
        }

        $slug = Str::slug($validated['title']);
        $count = Book::where('slug', 'LIKE', "{$slug}%")->count();
        $finalSlug = $count ? "{$slug}-".($count + 1) : $slug;

        Book::create([
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $finalSlug,
            'author' => $validated['author'],
            'publisher' => $validated['publisher'] ?? null,
            'isbn' => $validated['isbn'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'pages' => $validated['pages'] ?? null,
            'published_year' => $validated['published_year'] ?? null,
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverPath,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku baru berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $book = Book::findOrFail($id);
        $categories = Category::all();

        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('books', 'title')->ignore($book->id)->withoutTrashed(),
            ],
            'category_id' => ['nullable', 'exists:categories,id'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'isbn' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('books', 'isbn')->ignore($book->id)->withoutTrashed(),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'published_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul buku wajib diisi.',
            'title.unique' => 'Buku dengan judul ini sudah terdaftar pada buku lain.',
            'isbn.unique' => 'Nomor ISBN ini sudah terdaftar pada buku lain.',
            'author.required' => 'Nama penulis/pengarang wajib diisi.',
            'price.required' => 'Harga buku wajib diisi.',
            'stock.required' => 'Jumlah stok awal wajib diisi.',
        ]);

        $coverPath = $book->cover_image;
        if ($request->hasFile('cover_image')) {
            if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $coverPath = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update([
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'author' => $validated['author'],
            'publisher' => $validated['publisher'] ?? null,
            'isbn' => $validated['isbn'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'pages' => $validated['pages'] ?? null,
            'published_year' => $validated['published_year'] ?? null,
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverPath,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return redirect()->route('admin.books.index')
            ->with('success', 'Data buku "'.$book->title.'" berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $book = Book::findOrFail($id);
        $title = $book->title;
        // Soft delete ensures user order history retains full references safely!
        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', "Buku \"{$title}\" berhasil dihapus dari katalog toko.");
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $book = Book::findOrFail($id);
        $book->update(['is_active' => ! $book->is_active]);

        $statusStr = $book->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status buku \"{$book->title}\" berhasil {$statusStr}.");
    }
}
