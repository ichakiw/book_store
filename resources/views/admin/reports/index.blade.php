@extends('layouts.admin')

@section('title', 'Laporan Penjualan - BookStore Admin')
@section('page_title', 'Laporan Penjualan Toko')

@section('content')
<!-- Filter Period Bar -->
<div class="filter-bar">
    <form action="{{ route('admin.reports.index') }}" method="GET" class="d-flex align-center gap-3 flex-wrap flex-1">
        <div>
            <label class="text-xs text-muted font-bold uppercase block mb-1">Dari Tanggal:</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" style="padding: 6px 12px; width: auto;">
        </div>

        <div>
            <label class="text-xs text-muted font-bold uppercase block mb-1">Sampai Tanggal:</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" style="padding: 6px 12px; width: auto;">
        </div>

        <div>
            <label class="text-xs text-muted font-bold uppercase block mb-1">Status Pesanan:</label>
            <select name="status" class="form-select" style="padding: 6px 12px; width: auto;">
                <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Hanya Selesai (Lunas)</option>
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="diproses" {{ $status === 'diproses' ? 'selected' : '' }}>Diproses</option>
                <option value="dikirim" {{ $status === 'dikirim' ? 'selected' : '' }}>Dikirim</option>
            </select>
        </div>

        <div style="align-self: flex-end;">
            <button type="submit" class="btn btn-primary btn-sm">Tampilkan Laporan</button>
        </div>
    </form>

    <div style="align-self: flex-end;">
        <a href="{{ route('admin.reports.print', ['start_date' => $startDate, 'end_date' => $endDate, 'status' => $status]) }}" 
           class="btn btn-secondary btn-sm" target="_blank">
            🖨️ Cetak / Unduh Laporan
        </a>
    </div>
</div>

<!-- Report Summary Metrics -->
<div class="stat-cards-grid mb-4">
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Omset Penjualan</span>
            <span class="stat-value" style="color: var(--success); font-size: 1.55rem;">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </span>
            <span class="stat-sub">Periode yang dipilih</span>
        </div>
        <div class="stat-icon stat-icon-green">💰</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Transaksi</span>
            <span class="stat-value">{{ $totalOrders }}</span>
            <span class="stat-sub">Pesanan masuk</span>
        </div>
        <div class="stat-icon stat-icon-blue">🛒</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Buku Terjual</span>
            <span class="stat-value" style="color: var(--primary);">{{ $totalBooksSold }}</span>
            <span class="stat-sub">Total eksemplar buku</span>
        </div>
        <div class="stat-icon stat-icon-purple">📚</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Rata-rata Nilai Pesanan</span>
            <span class="stat-value" style="font-size: 1.35rem; color: var(--text-main);">
                Rp {{ number_format($averageOrderValue, 0, ',', '.') }}
            </span>
            <span class="stat-sub">AOV per transaksi</span>
        </div>
        <div class="stat-icon stat-icon-cyan">📊</div>
    </div>
</div>

<!-- 2-Column: Left Top Books, Right Transactions Table -->
<div class="d-grid" style="grid-template-columns: 1fr 2.5fr; gap: 24px; align-items: flex-start;">
    <!-- Top Selling Books -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header">
            <h3 class="card-title">🏆 Top Buku Terlaris</h3>
        </div>
        <div class="card-body">
            @forelse($topBooks as $idx => $tb)
                <div class="d-flex align-center justify-between gap-2 mb-3 pb-2" style="{{ !$loop->last ? 'border-bottom: 1px solid var(--border-light);' : '' }}">
                    <div class="d-flex align-center gap-2" style="min-width: 0; flex: 1;">
                        <span class="badge {{ $idx === 0 ? 'badge-warning' : 'badge-secondary' }}" style="font-weight: 800;">
                            #{{ $idx + 1 }}
                        </span>
                        <div style="font-weight: 700; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $tb->book_title }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-primary font-bold">{{ $tb->total_qty }} terjual</span>
                    </div>
                </div>
            @empty
                <div class="text-muted text-sm text-center py-3">Belum ada penjualan di periode ini.</div>
            @endforelse
        </div>
    </div>

    <!-- Detailed Transactions Table -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header">
            <h3 class="card-title">📋 Rincian Transaksi Penjualan</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th style="text-align: right;">Subtotal</th>
                            <th style="text-align: right;">Ongkir</th>
                            <th style="text-align: right;">Total Tagihan</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $o)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $o->id) }}" style="font-weight: 700;">
                                        {{ $o->order_number }}
                                    </a>
                                </td>
                                <td class="text-xs text-muted">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                                <td style="font-weight: 600;">{{ $o->recipient_name }}</td>
                                <td style="text-align: right;">{{ $o->formatted_total_amount }}</td>
                                <td style="text-align: right;">{{ $o->formatted_shipping_cost }}</td>
                                <td style="text-align: right; font-weight: 700; color: var(--primary);">
                                    {{ $o->formatted_grand_total }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge {{ $o->status_badge_class }}">
                                        {{ $o->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    Tidak ada data penjualan pada rentang tanggal yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
