@extends('layouts.admin')

@section('title', 'Kelola Buku - BookStore Admin')
@section('page_title', 'Kelola Koleksi Buku')

@section('content')
<!-- Filter & Header Actions -->
<div class="filter-bar">
    <form action="{{ route('admin.books.index') }}" method="GET" class="d-flex align-center gap-3 flex-wrap flex-1">
        <!-- Search -->
        <div style="min-width: 240px; flex: 1;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, penulis, ISBN..." class="form-control" style="padding: 8px 14px;">
        </div>

        <!-- Category Filter -->
        <div>
            <select name="category_id" class="form-select" style="padding: 8px 14px; width: auto;" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Stock Status Filter -->
        <div>
            <select name="stock_status" class="form-select" style="padding: 8px 14px; width: auto;" onchange="this.form.submit()">
                <option value="">Semua Kondisi Stok</option>
                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stok Menipis (≤ 5)</option>
                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        @if(request()->anyFilled(['search', 'category_id', 'stock_status']))
            <a href="{{ route('admin.books.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>

    <div>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary btn-sm">
            + Tambah Buku Baru
        </a>
    </div>
</div>

<!-- Books Table Card -->
<div class="card" style="border-radius: var(--radius-md);">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Cover</th>
                        <th>Judul &amp; Penulis</th>
                        <th>Kategori</th>
                        <th style="text-align: right;">Harga</th>
                        <th style="text-align: center;">Stok</th>
                        <th style="text-align: center;">Status Toko</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td>
                                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" 
                                     style="width: 44px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; line-height: 1.3;">
                                    {{ $book->title }}
                                </div>
                                <div class="text-xs text-muted">
                                    ✍️ {{ $book->author }} • ISBN: {{ $book->isbn ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ $book->category?->name ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--text-main);">
                                {{ $book->formatted_price }}
                            </td>
                            <td style="text-align: center;">
                                @if($book->stock > 5)
                                    <span class="badge badge-success">{{ $book->stock }} eks</span>
                                @elseif($book->stock > 0)
                                    <span class="badge badge-warning">{{ $book->stock }} eks (Menipis)</span>
                                @else
                                    <span class="badge badge-danger">Habis</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.books.toggle_status', $book->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="badge {{ $book->is_active ? 'badge-primary' : 'badge-secondary' }}" 
                                            style="cursor: pointer; border: none;" title="Klik untuk ubah status tampil di toko">
                                        {{ $book->is_active ? 'Aktif (Tampil)' : 'Nonaktif (Draft)' }}
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns">
                                    <a href="{{ route('books.show', $book->slug) }}" class="action-btn" title="Lihat di Toko" target="_blank">
                                        👁️
                                    </a>
                                    <a href="{{ route('admin.books.edit', $book->id) }}" class="action-btn" title="Edit Data Buku">
                                        ✏️
                                    </a>
                                    <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini? Riwayat pesanan lama pelanggan tidak akan terganggu.');" 
                                          style="margin: 0; display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Hapus Buku">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data buku yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-between align-center flex-wrap gap-3">
    <div class="text-xs text-muted">
        ℹ️ <em>Buku yang dihapus dari katalog admin tidak akan merusak riwayat transaksi pelanggan (riwayat belanja menyimpan snapshot nama, cover, dan harga).</em>
    </div>
    <div>
        {{ $books->links() }}
    </div>
</div>
@endsection
