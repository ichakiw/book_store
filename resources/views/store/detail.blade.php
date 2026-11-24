@extends('layouts.app')

@section('title', $book->title . ' — BookStore')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb & Back Navigation -->
    <div class="detail-nav-bar">
        <nav class="detail-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Beranda</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('books.index') }}">Katalog Buku</a>
            @if($book->category)
                <span class="breadcrumb-sep">›</span>
                <a href="{{ route('books.index', ['category' => $book->category->slug]) }}">{{ $book->category->name }}</a>
            @endif
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-current">{{ Str::limit($book->title, 40) }}</span>
        </nav>
        <a href="{{ route('books.index') }}" class="detail-back-link">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            <span>Kembali ke Katalog</span>
        </a>
    </div>

    <!-- Main Detail Showcase -->
    <div class="detail-container">
        <div class="detail-grid">
            <!-- Left Column: Book Visual Showcase -->
            <div class="detail-visual-col">
                <div class="detail-cover-stage">
                    <div class="detail-cover-wrapper">
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" id="mainBookCover" class="detail-cover-image" loading="eager">
                        @if($book->stock <= 0)
                            <div class="detail-cover-badge is-out">✕ Stok Habis</div>
                        @elseif($book->stock <= 5)
                            <div class="detail-cover-badge is-warning">⚡ Sisa {{ $book->stock }} eks</div>
                        @else
                            <div class="detail-cover-badge is-available">✓ Original</div>
                        @endif
                    </div>
                </div>

                <!-- Trust Badges Strip -->
                <div class="detail-trust-pills">
                    <div class="detail-trust-pill">
                        <span class="trust-icon">🛡️</span>
                        <span>100% Buku Original &amp; Resmi</span>
                    </div>
                    <div class="detail-trust-pill">
                        <span class="trust-icon">📦</span>
                        <span>Packing Bubble Wrap Tebal Gratis</span>
                    </div>
                    <div class="detail-trust-pill">
                        <span class="trust-icon">⚡</span>
                        <span>Pengiriman Cepat &amp; Terlacak</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Book Info & Action Panel -->
            <div class="detail-info-col">
                <!-- Category & Stock Status Header -->
                <div class="detail-meta-header">
                    <a href="{{ route('books.index', ['category' => $book->category?->slug]) }}" class="detail-category-tag">
                        <span>📚</span>
                        <span>{{ $book->category?->name ?? 'Kategori Umum' }}</span>
                    </a>

                    @if($book->stock > 5)
                        <span class="detail-stock-chip is-in-stock">
                            <span class="stock-dot"></span>
                            <span>Stok Tersedia ({{ $book->stock }} eks)</span>
                        </span>
                    @elseif($book->stock > 0)
                        <span class="detail-stock-chip is-limited-stock">
                            <span class="stock-dot"></span>
                            <span>Stok Terbatas (Sisa {{ $book->stock }} eks)</span>
                        </span>
                    @else
                        <span class="detail-stock-chip is-out-of-stock">
                            <span class="stock-dot"></span>
                            <span>Stok Habis</span>
                        </span>
                    @endif
                </div>

                <!-- Title & Author -->
                <h1 class="detail-book-title">{{ $book->title }}</h1>
                <div class="detail-book-author">
                    <span class="author-label">Penulis:</span>
                    <span class="author-name">{{ $book->author }}</span>
                </div>

                <!-- Price Row -->
                <div class="detail-price-box">
                    <div class="detail-price-val">{{ $book->formatted_price }}</div>
                    <div class="detail-price-sub">
                        <span class="price-guarantee">✨ Harga Resmi &amp; Jaminan 100% Asli</span>
                    </div>
                </div>

                <!-- Purchase Action Panel -->
                <div class="detail-purchase-panel">
                    @if($book->stock > 0)
                        <form action="{{ route('cart.add', $book->id) }}" method="POST" class="detail-buy-form">
                            @csrf
                            <div class="detail-buy-row">
                                <div class="detail-qty-control">
                                    <label class="detail-qty-label">Jumlah:</label>
                                    <div class="detail-stepper">
                                        <button type="button" class="stepper-btn" onclick="decreaseQty()" aria-label="Kurangi jumlah">−</button>
                                        <input type="number" name="quantity" id="detailQty" value="1" min="1" max="{{ $book->stock }}" class="stepper-input" readonly>
                                        <button type="button" class="stepper-btn" onclick="increaseQty({{ $book->stock }})" aria-label="Tambah jumlah">+</button>
                                    </div>
                                    <span class="detail-stock-limit">Maks. {{ $book->stock }} eks</span>
                                </div>

                                <button type="submit" class="detail-btn-cart">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                    <span>Tambah ke Keranjang</span>
                                </button>
                            </div>
                            <div class="detail-purchase-hint">
                                <span>ℹ️</span>
                                <span>Stok buku akan terpotong secara aman setelah pembayaran pesanan terverifikasi.</span>
                            </div>
                        </form>
                    @else
                        <div class="detail-out-of-stock-box">
                            <div class="out-icon">⚠️</div>
                            <div class="out-info">
                                <strong>Maaf, buku ini sedang habis terjual.</strong>
                                <p>Stok sedang dalam proses restock dari penerbit resmi. Anda dapat melihat buku menarik lainnya yang tersedia di bawah.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Book Specifications -->
                <div class="detail-section-block">
                    <h3 class="detail-subheading">Spesifikasi Buku</h3>
                    <div class="detail-specs-cards">
                        <div class="spec-card">
                            <span class="spec-card-icon">🏢</span>
                            <span class="spec-card-label">Penerbit</span>
                            <span class="spec-card-value" title="{{ $book->publisher }}">{{ $book->publisher ?: 'Penerbit Resmi' }}</span>
                        </div>
                        <div class="spec-card">
                            <span class="spec-card-icon">🏷️</span>
                            <span class="spec-card-label">Nomor ISBN</span>
                            <span class="spec-card-value" title="{{ $book->isbn }}">{{ $book->isbn ?: '-' }}</span>
                        </div>
                        <div class="spec-card">
                            <span class="spec-card-icon">📄</span>
                            <span class="spec-card-label">Jumlah Halaman</span>
                            <span class="spec-card-value">{{ $book->pages ? $book->pages . ' Halaman' : '-' }}</span>
                        </div>
                        <div class="spec-card">
                            <span class="spec-card-icon">📅</span>
                            <span class="spec-card-label">Tahun Terbit</span>
                            <span class="spec-card-value">{{ $book->published_year ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Book Description / Synopsis -->
                <div class="detail-section-block">
                    <h3 class="detail-subheading">Sinopsis &amp; Deskripsi</h3>
                    <div class="detail-description-content">
                        {{ $book->description ?: 'Belum ada sinopsis untuk buku ini.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Books Section -->
    @if($relatedBooks->count() > 0)
        <div class="detail-related-section">
            <div class="detail-related-header">
                <div>
                    <h3 class="detail-related-title">Rekomendasi Buku Terkait</h3>
                    <p class="detail-related-subtitle">Judul-judul pilihan lainnya dalam kategori {{ $book->category?->name ?? 'serupa' }}</p>
                </div>
                @if($book->category)
                    <a href="{{ route('books.index', ['category' => $book->category->slug]) }}" class="detail-related-all">
                        <span>Lihat Semua di Kategori Ini</span>
                        <span>→</span>
                    </a>
                @endif
            </div>

            <div class="catalog-grid">
                @foreach($relatedBooks as $rel)
                    <article class="catalog-card">
                        <a href="{{ route('books.show', $rel->slug) }}" class="catalog-card-cover">
                            <img src="{{ $rel->cover_url }}" alt="{{ $rel->title }}" loading="lazy">
                            @if($rel->stock <= 0)
                                <span class="catalog-card-flag is-out">Habis</span>
                            @endif
                        </a>
                        <div class="catalog-card-body">
                            <p class="catalog-card-meta">{{ $rel->category?->name ?? 'Umum' }}</p>
                            <a href="{{ route('books.show', $rel->slug) }}" class="catalog-card-title">{{ $rel->title }}</a>
                            <p class="catalog-card-author">{{ $rel->author }}</p>
                            <div class="catalog-card-foot">
                                <span class="catalog-card-price">{{ $rel->formatted_price }}</span>
                            </div>
                            <div class="catalog-card-actions">
                                <a href="{{ route('books.show', $rel->slug) }}" class="btn-card-detail">
                                    Detail
                                </a>
                                @if($rel->stock > 0)
                                    <form action="{{ route('cart.add', $rel->id) }}" method="POST" class="card-cart-form">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-card-cart" title="Tambah ke Keranjang">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                            <span>Keranjang</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn-card-cart is-disabled" disabled title="Stok Habis">
                                        <span>Habis</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function decreaseQty() {
    const input = document.getElementById('detailQty');
    if (!input) return;
    let val = parseInt(input.value) || 1;
    if (val > 1) {
        input.value = val - 1;
    }
}
function increaseQty(maxStock) {
    const input = document.getElementById('detailQty');
    if (!input) return;
    let val = parseInt(input.value) || 1;
    if (val < maxStock) {
        input.value = val + 1;
    }
}
</script>
@endpush
