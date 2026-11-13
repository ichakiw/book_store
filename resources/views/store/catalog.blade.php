@extends('layouts.app')

@section('title', 'Katalog Buku')

@section('content')
<div class="container py-5">
    <header class="catalog-header">
        <div>
            <p class="catalog-eyebrow">Koleksi</p>
            <h1 class="catalog-title">{{ $selectedCategory ? $selectedCategory->name : 'Katalog Buku' }}</h1>
            <p class="catalog-subtitle">{{ $books->total() }} judul tersedia</p>
        </div>
        <form action="{{ route('books.index') }}" method="GET" class="catalog-search">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('in_stock') === '1')
                <input type="hidden" name="in_stock" value="1">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau penulis..." class="catalog-search-input" aria-label="Cari buku">
            <button type="submit" class="catalog-search-btn" aria-label="Cari">→</button>
        </form>
    </header>

    <nav class="catalog-categories" aria-label="Kategori">
        <a href="{{ route('books.index', request()->except('category', 'page')) }}" class="catalog-chip {{ !request('category') ? 'is-active' : '' }}">Semua</a>
        @foreach($categories as $cat)
            <a href="{{ route('books.index', array_merge(request()->except('page'), ['category' => $cat->slug])) }}" class="catalog-chip {{ request('category') === $cat->slug ? 'is-active' : '' }}">{{ $cat->name }}</a>
        @endforeach
    </nav>

    <div class="catalog-toolbar">
        <form action="{{ route('books.index') }}" method="GET" class="catalog-toolbar-form">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <label class="catalog-check">
                <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') === '1' ? 'checked' : '' }} onchange="this.form.submit()">
                <span>Stok tersedia</span>
            </label>
            <select name="sort" class="catalog-select" onchange="this.form.submit()" aria-label="Urutkan">
                <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                <option value="harga_asc" {{ request('sort') === 'harga_asc' ? 'selected' : '' }}>Harga terendah</option>
                <option value="harga_desc" {{ request('sort') === 'harga_desc' ? 'selected' : '' }}>Harga tertinggi</option>
                <option value="judul_asc" {{ request('sort') === 'judul_asc' ? 'selected' : '' }}>A–Z</option>
            </select>
        </form>
        @if(request()->anyFilled(['search', 'category', 'in_stock', 'sort']))
            <a href="{{ route('books.index') }}" class="catalog-reset">Reset</a>
        @endif
    </div>

    @if($books->count() > 0)
        <div class="catalog-grid">
            @foreach($books as $book)
                <article class="catalog-card">
                    <a href="{{ route('books.show', $book->slug) }}" class="catalog-card-cover">
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" loading="lazy">
                        @if($book->stock <= 0)
                            <span class="catalog-card-flag">Habis</span>
                        @endif
                    </a>
                    <div class="catalog-card-body">
                        <p class="catalog-card-meta">{{ $book->category?->name ?? 'Umum' }}</p>
                        <a href="{{ route('books.show', $book->slug) }}" class="catalog-card-title">{{ $book->title }}</a>
                        <p class="catalog-card-author">{{ $book->author }}</p>
                        <div class="catalog-card-foot">
                            <span class="catalog-card-price">{{ $book->formatted_price }}</span>
                            <a href="{{ route('books.show', $book->slug) }}" class="catalog-card-link">Lihat</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-center">
            {{ $books->links() }}
        </div>
    @else
        <div class="catalog-empty">
            <h3>Tidak ada buku ditemukan</h3>
            <p>Coba ubah kata kunci atau filter pencarian.</p>
            <a href="{{ route('books.index') }}" class="btn btn-primary btn-sm">Lihat semua buku</a>
        </div>
    @endif
</div>
@endsection
