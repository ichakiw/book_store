@extends('layouts.app')

@section('title', 'Keranjang Belanja - BookStore')

@section('content')
<div class="container py-4">
    <div class="d-flex align-center justify-between mb-4">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 4px;">Keranjang Belanja</h1>
            <p class="text-muted text-sm">Periksa kembali daftar buku yang akan Anda beli sebelum checkout.</p>
        </div>

        @if($cartItems->isNotEmpty())
            <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    🗑️ Kosongkan Keranjang
                </button>
            </form>
        @endif
    </div>

    @if($cartItems->isNotEmpty())
        <div class="cart-layout">
            <!-- Items Column -->
            <div>
                @if($hasOutOfStock)
                    <div class="alert alert-warning mb-3">
                        <span>⚠️</span>
                        <div>Beberapa item di keranjang memiliki stok yang tidak mencukupi atau sudah tidak tersedia. Harap sesuaikan jumlah sebelum checkout.</div>
                    </div>
                @endif

                @foreach($cartItems as $item)
                    <div class="cart-item-card">
                        <img src="{{ $item->book ? $item->book->cover_url : asset('images/default-book.svg') }}" 
                             alt="{{ $item->book->title ?? 'Buku' }}" class="cart-thumb">

                        <div style="flex: 1; min-width: 0;">
                            <div class="text-xs text-primary font-bold uppercase mb-1">
                                {{ $item->book?->category?->name ?? 'Kategori' }}
                            </div>
                            <h4 style="font-size: 1.05rem; margin-bottom: 4px; line-height: 1.3;">
                                @if($item->book)
                                    <a href="{{ route('books.show', $item->book->slug) }}" style="color: var(--text-main);">
                                        {{ $item->book->title }}
                                    </a>
                                @else
                                    <span style="color: var(--danger); text-decoration: line-through;">Buku Sudah Dihapus</span>
                                @endif
                            </h4>
                            <div class="text-muted text-xs mb-2">Penulis: {{ $item->book->author ?? '-' }}</div>

                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.98rem;">
                                {{ $item->book ? $item->book->formatted_price : '-' }}
                            </div>

                            @if($item->book && $item->quantity > $item->book->stock)
                                <span class="badge badge-danger mt-1">Stok hanya tersisa {{ $item->book->stock }}!</span>
                            @endif
                        </div>

                        <!-- Quantity Controls -->
                        <div class="d-flex align-center gap-2">
                            @if($item->book && $item->book->is_active)
                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-flex align-center">
                                    @csrf
                                    <div class="quantity-input-group" style="scale: 0.9;">
                                        <button type="submit" name="action" value="decrease" class="qty-btn">-</button>
                                        <input type="text" readonly value="{{ $item->quantity }}" class="qty-input">
                                        <button type="submit" name="action" value="increase" class="qty-btn" 
                                                {{ $item->quantity >= $item->book->stock ? 'disabled' : '' }}>+</button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        <!-- Subtotal -->
                        <div style="min-width: 120px; text-align: right;">
                            <div class="text-xs text-muted mb-1">Subtotal</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                                {{ $item->formatted_subtotal }}
                            </div>
                        </div>

                        <!-- Delete Button -->
                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" onsubmit="return confirm('Hapus buku ini dari keranjang?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn delete" title="Hapus Item">
                                🗑️
                            </button>
                        </form>
                    </div>
                @endforeach

                <div style="padding: 16px; background-color: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed #cbd5e1; font-size: 0.85rem; color: var(--text-muted);">
                    💡 <em>Catatan: Menambahkan buku ke dalam keranjang belum mengurangi stok buku di toko. Stok buku baru akan terpotong secara resmi setelah Anda menyelesaikan pembayaran transaksi.</em>
                </div>
            </div>

            <!-- Summary Column -->
            <div>
                <div class="summary-card">
                    <h3 style="font-size: 1.25rem; margin-bottom: 18px;">Ringkasan Belanja</h3>

                    <div class="summary-row">
                        <span>Total Item</span>
                        <strong style="color: var(--text-main);">{{ $cartItems->sum('quantity') }} buku</strong>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal Produk</span>
                        <strong style="color: var(--text-main);">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Ongkos Kirim Flat</span>
                        <strong style="color: var(--text-main);">Rp {{ number_format($shippingCost, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-total">
                        <span>Total Pembayaran</span>
                        <span style="color: var(--primary);">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="mt-4">
                        @if($hasOutOfStock)
                            <button class="btn btn-primary btn-block btn-lg" disabled title="Perbaiki item stok habis terlebih dahulu">
                                🔒 Stok Tidak Mencukupi
                            </button>
                        @else
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg" style="box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                                Lanjut ke Checkout →
                            </a>
                        @endif
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('books.index') }}" class="text-sm font-semibold text-muted">
                            ← Lanjut Belanja Buku Lainnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="card text-center py-5" style="border-radius: var(--radius-lg);">
            <div class="card-body">
                <div style="font-size: 4rem; margin-bottom: 16px;">🛒</div>
                <h3 style="font-size: 1.4rem; margin-bottom: 8px;">Keranjang Belanja Masih Kosong</h3>
                <p class="text-muted text-sm" style="max-width: 440px; margin: 0 auto 24px auto;">
                    Anda belum memasukkan buku apa pun ke dalam keranjang belanja. Yuk, temukan buku menarik dan inspiratif di katalog kami!
                </p>
                <a href="{{ route('books.index') }}" class="btn btn-primary btn-lg">
                    Jelajahi Katalog Buku Sekarang →
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
