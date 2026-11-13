@extends('layouts.admin')

@section('title', 'Kelola Stok Buku - BookStore Admin')
@section('page_title', 'Kelola Stok Buku')

@section('content')
<!-- Stock Summary Metric Cards -->
<div class="stat-cards-grid mb-4">
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Judul Buku</span>
            <span class="stat-value">{{ $stats['total'] }}</span>
        </div>
        <div class="stat-icon stat-icon-blue">📦</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Stok Aman (> 5)</span>
            <span class="stat-value" style="color: var(--success);">{{ $stats['healthy'] }}</span>
        </div>
        <div class="stat-icon stat-icon-green">✓</div>
    </div>

    <div class="stat-card" style="{{ $stats['low'] > 0 ? 'border-color: var(--warning); background-color: var(--warning-light);' : '' }}">
        <div class="stat-info">
            <span class="stat-label">Stok Menipis (1 - 5)</span>
            <span class="stat-value" style="color: var(--warning);">{{ $stats['low'] }}</span>
        </div>
        <div class="stat-icon stat-icon-orange">⚡</div>
    </div>

    <div class="stat-card" style="{{ $stats['out'] > 0 ? 'border-color: var(--danger-border); background-color: var(--danger-light);' : '' }}">
        <div class="stat-info">
            <span class="stat-label">Stok Habis (0)</span>
            <span class="stat-value" style="color: var(--danger);">{{ $stats['out'] }}</span>
        </div>
        <div class="stat-icon stat-icon-red">✕</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form action="{{ route('admin.stocks.index') }}" method="GET" class="d-flex align-center gap-3 flex-wrap flex-1">
        <div style="min-width: 240px; flex: 1;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul buku atau penulis..." class="form-control" style="padding: 8px 14px;">
        </div>

        <div>
            <select name="category_id" class="form-select" style="padding: 8px 14px; width: auto;" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="filter" class="form-select" style="padding: 8px 14px; width: auto;" onchange="this.form.submit()">
                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Semua Kondisi</option>
                <option value="low" {{ $filter === 'low' ? 'selected' : '' }}>Stok Menipis (1 - 5)</option>
                <option value="out" {{ $filter === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        @if(request()->anyFilled(['search', 'category_id', 'filter']))
            <a href="{{ route('admin.stocks.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Stock Table Card -->
<div class="card" style="border-radius: var(--radius-md);">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Cover</th>
                        <th>Judul Buku &amp; Penulis</th>
                        <th>Kategori</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: center;">Jumlah Stok Saat Ini</th>
                        <th style="text-align: center;">Status Indikator</th>
                        <th style="text-align: right;">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td>
                                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" 
                                     style="width: 40px; height: 54px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main);">
                                    {{ $book->title }}
                                </div>
                                <div class="text-xs text-muted">Penulis: {{ $book->author }}</div>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ $book->category?->name ?? 'Kategori' }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                {{ $book->formatted_price }}
                            </td>
                            <td style="text-align: center; font-size: 1.15rem; font-weight: 800; color: {{ $book->stock <= 0 ? 'var(--danger)' : ($book->stock <= 5 ? 'var(--warning)' : 'var(--text-main)') }};">
                                {{ $book->stock }} eks
                            </td>
                            <td style="text-align: center;">
                                @if($book->stock > 5)
                                    <span class="badge badge-success">✓ Stok Aman</span>
                                @elseif($book->stock > 0)
                                    <span class="badge badge-warning">⚡ Menipis</span>
                                @else
                                    <span class="badge badge-danger">✕ Habis</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="openStockModal({{ $book->id }}, '{{ addslashes($book->title) }}', {{ $book->stock }})">
                                    ⚙️ Atur Stok
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data buku pada kondisi stok ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-center">
    {{ $books->links() }}
</div>

<!-- Stock Adjustment Modal -->
<div class="modal-overlay" id="stockModalOverlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">⚙️ Perbarui Stok Buku</h3>
            <button type="button" class="modal-close" onclick="closeStockModal()">&times;</button>
        </div>
        <form id="stockForm" method="POST">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom: 16px; padding: 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div class="text-xs text-muted font-bold uppercase">Judul Buku</div>
                    <div id="modalBookTitle" style="font-weight: 700; color: var(--text-main); font-size: 1rem;">-</div>
                    <div class="text-xs text-muted mt-1">Stok saat ini: <strong id="modalCurrentStock" style="color: var(--primary);">-</strong> eks</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Metode Penyesuaian Stok</label>
                    <div class="d-flex gap-3">
                        <label class="form-check" style="flex: 1; padding: 10px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
                            <input type="radio" name="stock_type" value="add" checked>
                            <div>
                                <div style="font-weight: 700;">Tambah Stok (+)</div>
                                <div class="text-xs text-muted">Misal restock 20 eks</div>
                            </div>
                        </label>
                        <label class="form-check" style="flex: 1; padding: 10px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
                            <input type="radio" name="stock_type" value="set">
                            <div>
                                <div style="font-weight: 700;">Set Nilai Baru (=)</div>
                                <div class="text-xs text-muted">Ubah total stok</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Jumlah Jumlah (Eks)</label>
                    <input type="number" name="amount" id="stockAmountInput" min="0" class="form-control" placeholder="Contoh: 10" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeStockModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Stok Baru</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openStockModal(id, title, currentStock) {
    document.getElementById('stockForm').action = '/admin/stok/' + id;
    document.getElementById('modalBookTitle').innerText = title;
    document.getElementById('modalCurrentStock').innerText = currentStock;
    document.getElementById('stockAmountInput').value = '';
    document.getElementById('stockModalOverlay').classList.add('active');
    setTimeout(() => document.getElementById('stockAmountInput').focus(), 100);
}

function closeStockModal() {
    document.getElementById('stockModalOverlay').classList.remove('active');
}
</script>
@endpush
