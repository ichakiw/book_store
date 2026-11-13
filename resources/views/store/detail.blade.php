@extends('layouts.app')

@section('title', $book->title . ' - BookStore')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <div style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 20px;">
        <a href="{{ route('books.index') }}">Katalog Buku</a> / 
        @if($book->category)
            <a href="{{ route('books.index', ['category' => $book->category->slug]) }}">{{ $book->category->name }}</a> /
        @endif
        <span style="color: var(--text-main); font-weight: 600;">{{ Str::limit($book->title, 40) }}</span>
    </div>

    <!-- Main Detail Card -->
    <div class="detail-grid">
        <!-- Book Cover Column -->
        <div>
            <div class="detail-cover-box">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" id="mainBookCover">
            </div>

            <div class="d-flex align-center justify-center gap-2 mt-3 text-muted text-xs">
                <span>🛡️ 100% Produk Original Bergaransi</span>
            </div>
        </div>

        <!-- Book Info & Action Column -->
        <div class="d-flex flex-column">
            <!-- Category & Stock Status -->
            <div class="d-flex align-center justify-between gap-2 mb-2">
                <span class="badge badge-primary">{{ $book->category?->name ?? 'Kategori Umum' }}</span>
                @if($book->stock > 5)
                    <span class="badge badge-success">✓ Stok Tersedia ({{ $book->stock }} eks)</span>
                @elseif($book->stock > 0)
                    <span class="badge badge-warning">⚡ Stok Terbatas (Tersisa {{ $book->stock }} eks)</span>
                @else
                    <span class="badge badge-danger">✕ Stok Habis</span>
                @endif
            </div>

            <!-- Title & Author -->
            <h1 style="font-size: 2.1rem; margin-bottom: 8px; line-height: 1.25;">
                {{ $book->title }}
            </h1>
            <div style="font-size: 1rem; color: var(--text-muted); margin-bottom: 18px;">
                Penulis: <strong style="color: var(--text-main);">{{ $book->author }}</strong>
            </div>

            <!-- Price -->
            <div style="padding: 14px 20px; background-color: var(--bg-page); border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 20px;">
                <div class="text-xs text-muted text-uppercase font-bold" style="letter-spacing: 0.5px;">Harga Buku</div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--primary);">
                    {{ $book->formatted_price }}
                </div>
            </div>

            <!-- Add to Cart Form -->
            @if($book->stock > 0)
                <form action="{{ route('cart.add', $book->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="d-flex align-center gap-3 mb-3 flex-wrap">
                        <label class="form-label" style="margin: 0; font-size: 0.95rem;">Jumlah:</label>
                        <div class="quantity-input-group">
                            <button type="button" class="qty-btn" onclick="decreaseQty()">-</button>
                            <input type="number" name="quantity" id="detailQty" value="1" min="1" max="{{ $book->stock }}" class="qty-input">
                            <button type="button" class="qty-btn" onclick="increaseQty({{ $book->stock }})">+</button>
                        </div>
                        <span class="text-xs text-muted">Maksimal {{ $book->stock }} eks</span>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg" style="flex: 1; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                            🛒 Tambah ke Keranjang
                        </button>
                    </div>
                    <div class="form-hint" style="margin-top: 8px;">
                        ℹ️ <em>Stok buku belum terpotong ketika dimasukkan ke keranjang belanja. Stok akan otomatis berkurang setelah konfirmasi pembayaran transaksi.</em>
                    </div>
                </form>
            @else
                <div class="alert alert-danger mb-4">
                    <span>⚠️</span>
                    <div>
                        <strong>Maaf, buku ini sedang habis terjual.</strong>
                        <div class="text-xs mt-1">Silakan cek kembali beberapa hari lagi atau pilih buku lainnya yang tersedia.</div>
                    </div>
                </div>
            @endif

            <!-- Specifications Table Grid -->
            <h4 style="font-size: 1.05rem; margin-top: 10px; margin-bottom: 10px;">Spesifikasi Buku</h4>
            <div class="detail-specs-grid">
                <div class="spec-item">
                    <span class="spec-label">Penerbit</span>
                    <span class="spec-value">{{ $book->publisher ?? '-' }}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Nomor ISBN</span>
                    <span class="spec-value">{{ $book->isbn ?? '-' }}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Jumlah Halaman</span>
                    <span class="spec-value">{{ $book->pages ? $book->pages . ' Halaman' : '-' }}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Tahun Terbit</span>
                    <span class="spec-value">{{ $book->published_year ?? '-' }}</span>
                </div>
            </div>

            <!-- Description -->
            <h4 style="font-size: 1.05rem; margin-top: 14px; margin-bottom: 8px;">Sinopsis &amp; Deskripsi</h4>
            <div style="font-size: 0.95rem; line-height: 1.7; color: var(--secondary); white-space: pre-line;">
                {{ $book->description ?? 'Belum ada sinopsis untuk buku ini.' }}
            </div>
        </div>
    </div>

    <!-- Related Books Section -->
    @if($relatedBooks->count() > 0)
        <div class="mt-5">
            <h3 style="font-size: 1.4rem; margin-bottom: 18px;">Buku Terkait Lainnya</h3>
            <div class="book-grid">
                @foreach($relatedBooks as $rel)
                    <div class="book-card">
                        <div class="book-cover-wrap">
                            <img src="{{ $rel->cover_url }}" alt="{{ $rel->title }}" class="book-cover-img" loading="lazy">
                        </div>
                        <div class="book-content">
                            <div class="book-category">{{ $rel->category?->name ?? 'Umum' }}</div>
                            <a href="{{ route('books.show', $rel->slug) }}" class="book-title" title="{{ $rel->title }}">
                                {{ $rel->title }}
                            </a>
                            <div class="book-author">✍️ {{ $rel->author }}</div>
                            <div class="book-footer">
                                <div class="book-price">{{ $rel->formatted_price }}</div>
                                <a href="{{ route('books.show', $rel->slug) }}" class="btn btn-outline btn-sm">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
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
    let val = parseInt(input.value) || 1;
    if (val > 1) {
        input.value = val - 1;
    }
}
function increaseQty(maxStock) {
    const input = document.getElementById('detailQty');
    let val = parseInt(input.value) || 1;
    if (val < maxStock) {
        input.value = val + 1;
    }
}
</script>
@endpush
