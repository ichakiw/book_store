<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('books')->latest()->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->withoutTrashed(),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
                Rule::unique('categories', 'description')->withoutTrashed(),
            ],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah terdaftar atau duplikat. Silakan gunakan nama lain.',
            'description.unique' => 'Deskripsi kategori ini sudah ada atau duplikat pada kategori lain. Silakan gunakan deskripsi yang berbeda.',
        ]);

        Category::create([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id)->withoutTrashed(),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
                Rule::unique('categories', 'description')->ignore($category->id)->withoutTrashed(),
            ],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah terdaftar atau duplikat. Silakan gunakan nama lain.',
            'description.unique' => 'Deskripsi kategori ini sudah ada atau duplikat pada kategori lain. Silakan gunakan deskripsi yang berbeda.',
        ]);

        $category->update([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori "'.$category->name.'" berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $name = $category->name;

        // Unlink or soft delete category
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }
}
