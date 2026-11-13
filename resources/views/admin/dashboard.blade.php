@extends('layouts.admin')

@section('title', 'Admin Dashboard - BookStore')
@section('page_title', 'Dashboard Overview')

@section('content')
<!-- Metric Cards Grid -->
<div class="stat-cards-grid">
    <!-- Total Revenue -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Pendapatan</span>
            <span class="stat-value" style="color: var(--success); font-size: 1.55rem;">
                Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
            </span>
            <span class="stat-sub">Dari pesanan berstatus selesai</span>
        </div>
        <div class="stat-icon stat-icon-green">💰</div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Pesanan</span>
            <span class="stat-value">{{ $stats['total_orders'] }}</span>
            <span class="stat-sub">Seluruh transaksi tercatat</span>
        </div>
        <div class="stat-icon stat-icon-blue">🛒</div>
    </div>

    <!-- Need Confirmation -->
    <div class="stat-card" style="{{ $stats['pending_confirmation'] > 0 ? 'border-color: var(--warning); background-color: var(--warning-light);' : '' }}">
        <div class="stat-info">
            <span class="stat-label">Perlu Konfirmasi</span>
            <span class="stat-value" style="color: var(--warning);">{{ $stats['pending_confirmation'] }}</span>
            <span class="stat-sub">
                <a href="{{ route('admin.orders.index', ['status' => 'menunggu_konfirmasi']) }}" style="font-weight: 700; color: var(--warning-hover);">
                    Periksa Bukti Bayar →
                </a>
            </span>
        </div>
        <div class="stat-icon stat-icon-orange">⏳</div>
    </div>

    <!-- Need Shipping -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Perlu Dikirim</span>
            <span class="stat-value" style="color: #0891b2;">{{ $stats['needs_shipping'] }}</span>
            <span class="stat-sub">
                <a href="{{ route('admin.orders.index', ['status' => 'diproses']) }}" style="font-weight: 700; color: #0e7490;">
                    Input Nomor Resi →
                </a>
            </span>
        </div>
        <div class="stat-icon stat-icon-cyan">🚚</div>
    </div>

    <!-- Total Books -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Judul Buku</span>
            <span class="stat-value">{{ $stats['total_books'] }}</span>
            <span class="stat-sub">Dalam {{ $stats['total_categories'] }} kategori</span>
        </div>
        <div class="stat-icon stat-icon-purple">📚</div>
    </div>

    <!-- Low Stock Alert -->
    <div class="stat-card" style="{{ $stats['low_stock_books'] > 0 ? 'border-color: var(--danger-border);' : '' }}">
        <div class="stat-info">
            <span class="stat-label">Stok Menipis (≤ 5)</span>
            <span class="stat-value" style="color: var(--danger);">{{ $stats['low_stock_books'] }}</span>
            <span class="stat-sub">
                <a href="{{ route('admin.stocks.index', ['filter' => 'low']) }}" style="font-weight: 700; color: var(--danger);">
                    Restock Sekarang →
                </a>
            </span>
        </div>
        <div class="stat-icon stat-icon-red">⚠️</div>
    </div>

    <!-- Total Customers -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Pelanggan Terdaftar</span>
            <span class="stat-value">{{ $stats['total_customers'] }}</span>
            <span class="stat-sub">Pengguna aktif toko</span>
        </div>
        <div class="stat-icon stat-icon-blue">👥</div>
    </div>
</div>

<!-- 2 Tables: Left Recent Orders, Right Low Stock Alert -->
<div class="d-grid" style="grid-template-columns: 2fr 1.2fr; gap: 24px; align-items: flex-start;">
    <!-- Recent Orders Card -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header d-flex justify-between align-center">
            <h3 class="card-title">🛒 Pesanan Terbaru Masuk</h3>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">Semua Pesanan →</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Pelanggan</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $o)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $o->id) }}" style="font-weight: 700;">
                                        {{ $o->order_number }}
                                    </a>
                                    <div class="text-xs text-muted">{{ $o->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $o->recipient_name }}</div>
                                    <div class="text-xs text-muted">{{ $o->recipient_phone }}</div>
                                </td>
                                <td style="font-weight: 700;">
                                    {{ $o->formatted_grand_total }}
                                </td>
                                <td>
                                    <span class="badge {{ $o->status_badge_class }}">
                                        {{ $o->status_label }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="btn btn-primary btn-sm">
                                        Kelola
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada pesanan masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert Card -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header d-flex justify-between align-center">
            <h3 class="card-title">⚠️ Peringatan Stok Menipis</h3>
            <a href="{{ route('admin.stocks.index') }}" class="text-xs font-bold text-danger">Kelola Stok →</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Judul Buku</th>
                            <th style="text-align: center;">Sisa</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockBooks as $b)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; line-height: 1.3;">{{ Str::limit($b->title, 26) }}</div>
                                    <div class="text-xs text-muted">{{ $b->category?->name ?? 'Kategori' }}</div>
                                </td>
                                <td style="text-align: center;">
                                    @if($b->stock <= 0)
                                        <span class="badge badge-danger">0 (Habis)</span>
                                    @else
                                        <span class="badge badge-warning">{{ $b->stock }} eks</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin.stocks.index', ['search' => $b->title]) }}" class="btn btn-secondary btn-sm">
                                        + Tambah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-success">
                                    ✓ Semua stok buku dalam kondisi aman!
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
