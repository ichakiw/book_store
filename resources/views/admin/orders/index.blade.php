@extends('layouts.admin')

@section('title', 'Kelola Pesanan - BookStore Admin')
@section('page_title', 'Kelola Pesanan & Transaksi')

@section('content')
<!-- Page Header Info -->
<div class="d-flex align-center justify-between flex-wrap gap-3 mb-4">
    <div>
        <p class="text-muted text-sm" style="margin: 0;">
            Pantau pembayaran, verifikasi bukti transfer, dan perbarui pengiriman seluruh pesanan buku secara real-time.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <span class="badge badge-primary" style="font-size: 0.82rem; padding: 6px 12px;">
            📊 Total: {{ $statusCounts['all'] }} Transaksi
        </span>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
            📈 Laporan Penjualan
        </a>
    </div>
</div>

<!-- 4 Modern KPI Highlights -->
<div class="orders-kpi-grid">
    <!-- Total Orders -->
    <div class="orders-kpi-card kpi-blue">
        <div>
            <div class="text-xs text-muted font-bold uppercase" style="letter-spacing: 0.5px; margin-bottom: 4px;">
                Semua Pesanan
            </div>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); line-height: 1.1;">
                {{ $kpis['total_orders'] }}
            </div>
            <div class="text-xs text-muted mt-1">Keseluruhan data pesanan</div>
        </div>
        <div class="orders-kpi-icon" style="background: var(--primary-light); color: var(--primary);">
            🛒
        </div>
    </div>

    <!-- Need Confirmation -->
    <div class="orders-kpi-card kpi-amber" style="{{ $kpis['pending_confirmation'] > 0 ? 'background: #fffbeb; border-color: #fde68a;' : '' }}">
        <div>
            <div class="text-xs font-bold uppercase" style="color: #b45309; letter-spacing: 0.5px; margin-bottom: 4px;">
                Perlu Konfirmasi
            </div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #b45309; line-height: 1.1;">
                {{ $kpis['pending_confirmation'] }}
            </div>
            <div class="text-xs mt-1" style="color: #92400e;">
                @if($kpis['pending_confirmation'] > 0)
                    ⚠️ Ada bukti bayar menunggu review!
                @else
                    Semua pembayaran terverifikasi
                @endif
            </div>
        </div>
        <div class="orders-kpi-icon" style="background: #fef3c7; color: #d97706;">
            ⏳
        </div>
    </div>

    <!-- Ready to Ship -->
    <div class="orders-kpi-card kpi-cyan">
        <div>
            <div class="text-xs font-bold uppercase" style="color: #0e7490; letter-spacing: 0.5px; margin-bottom: 4px;">
                Perlu Dikirim
            </div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #0e7490; line-height: 1.1;">
                {{ $kpis['processing'] }}
            </div>
            <div class="text-xs text-muted mt-1">Siap dikemas &amp; diberi resi</div>
        </div>
        <div class="orders-kpi-icon" style="background: #e0f2fe; color: #0284c7;">
            🚚
        </div>
    </div>

    <!-- Completed Revenue -->
    <div class="orders-kpi-card kpi-green">
        <div>
            <div class="text-xs font-bold uppercase" style="color: #065f46; letter-spacing: 0.5px; margin-bottom: 4px;">
                Omset Selesai
            </div>
            <div style="font-size: 1.45rem; font-weight: 800; color: #059669; line-height: 1.2;">
                Rp {{ number_format($kpis['total_revenue'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-muted mt-1">Dari transaksi berstatus selesai</div>
        </div>
        <div class="orders-kpi-icon" style="background: #d1fae5; color: #059669;">
            💰
        </div>
    </div>
</div>

<!-- Modern Scrollable Status Navigation Tabs -->
<div class="orders-nav-scroller">
    <a href="{{ route('admin.orders.index', request()->except('status', 'page')) }}" 
       class="orders-nav-pill {{ !$status ? 'active' : '' }}">
        <span>📋 Semua Pesanan</span>
        <span class="pill-count">{{ $statusCounts['all'] }}</span>
    </a>

    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'menunggu_pembayaran'])) }}" 
       class="orders-nav-pill {{ $status === 'menunggu_pembayaran' ? 'active' : '' }}">
        <span>💳 Menunggu Pembayaran</span>
        <span class="pill-count">{{ $statusCounts['menunggu_pembayaran'] }}</span>
    </a>

    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'menunggu_konfirmasi'])) }}" 
       class="orders-nav-pill {{ $statusCounts['menunggu_konfirmasi'] > 0 ? 'warning-highlight' : '' }} {{ $status === 'menunggu_konfirmasi' ? 'active' : '' }}">
        <span>⚠️ Menunggu Konfirmasi</span>
        <span class="pill-count" style="{{ $statusCounts['menunggu_konfirmasi'] > 0 && $status !== 'menunggu_konfirmasi' ? 'background: #f59e0b; color: #ffffff;' : '' }}">
            {{ $statusCounts['menunggu_konfirmasi'] }}
        </span>
    </a>

    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'diproses'])) }}" 
       class="orders-nav-pill {{ $status === 'diproses' ? 'active' : '' }}">
        <span>⚙️ Diproses</span>
        <span class="pill-count">{{ $statusCounts['diproses'] }}</span>
    </a>

    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'dikirim'])) }}" 
       class="orders-nav-pill {{ $status === 'dikirim' ? 'active' : '' }}">
        <span>🚚 Sedang Dikirim</span>
        <span class="pill-count">{{ $statusCounts['dikirim'] }}</span>
    </a>

    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'selesai'])) }}" 
       class="orders-nav-pill {{ $status === 'selesai' ? 'active' : '' }}">
        <span>✓ Selesai</span>
        <span class="pill-count">{{ $statusCounts['selesai'] }}</span>
    </a>

    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'pembayaran_ditolak'])) }}" 
       class="orders-nav-pill {{ $status === 'pembayaran_ditolak' ? 'active' : '' }}">
        <span>✕ Ditolak</span>
        <span class="pill-count">{{ $statusCounts['pembayaran_ditolak'] }}</span>
    </a>

    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'dibatalkan'])) }}" 
       class="orders-nav-pill {{ $status === 'dibatalkan' ? 'active' : '' }}">
        <span>🚫 Dibatalkan</span>
        <span class="pill-count">{{ $statusCounts['dibatalkan'] }}</span>
    </a>
</div>

<!-- Modern Filter & Search Bar -->
<div class="card mb-4" style="border-radius: var(--radius-md); box-shadow: var(--shadow-xs);">
    <div class="card-body" style="padding: 16px 20px;">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex align-center justify-between flex-wrap gap-3">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif

            <div class="d-flex align-center gap-3 flex-wrap flex-1">
                <!-- Search Input with icon -->
                <div style="min-width: 280px; flex: 1; position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); pointer-events: none;">🔍</span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari no. invoice, nama pembeli, nomor HP, atau kota..." 
                           class="form-control" style="padding: 9px 14px 9px 38px; border-radius: var(--radius-sm);">
                </div>

                <!-- Sort Dropdown -->
                <div class="d-flex align-center gap-2">
                    <label class="text-xs text-muted font-bold uppercase" style="white-space: nowrap;">Urutkan:</label>
                    <select name="sort" class="form-select" style="width: auto; padding: 9px 14px; font-size: 0.88rem;" onchange="this.form.submit()">
                        <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru (Default)</option>
                        <option value="terlama" {{ $sort === 'terlama' ? 'selected' : '' }}>Terlama</option>
                        <option value="nominal_desc" {{ $sort === 'nominal_desc' ? 'selected' : '' }}>Nominal: Tertinggi</option>
                        <option value="nominal_asc" {{ $sort === 'nominal_asc' ? 'selected' : '' }}>Nominal: Terendah</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm" style="padding: 9px 18px;">
                    Cari &amp; Terapkan
                </button>

                @if(request()->anyFilled(['search', 'sort', 'status']))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm" style="padding: 9px 14px;" title="Reset seluruh filter">
                        ✕ Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Active Filter Chips -->
@if(request('search'))
    <div class="mb-3 d-flex align-center gap-2">
        <span class="text-sm text-muted">Hasil pencarian untuk:</span>
        <span class="badge badge-primary font-bold">"{{ request('search') }}"</span>
        <a href="{{ route('admin.orders.index', request()->except('search')) }}" class="text-xs text-danger" style="margin-left: 4px;">✕ Hapus</a>
    </div>
@endif

<!-- Modern Orders Table Card -->
<div class="card" style="border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="modern-orders-table">
                <thead>
                    <tr>
                        <th style="width: 190px;">No. Invoice &amp; Waktu</th>
                        <th style="min-width: 220px;">Pembeli &amp; Alamat Tujuan</th>
                        <th style="min-width: 240px;">Buku yang Dipesan</th>
                        <th style="min-width: 170px; text-align: right;">Total &amp; Pembayaran</th>
                        <th style="width: 170px; text-align: center;">Status Pesanan</th>
                        <th style="width: 150px; text-align: right;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <!-- 1. Invoice & Date -->
                            <td>
                                <div class="invoice-badge-box">
                                    <div class="d-flex align-center gap-1">
                                        <a href="{{ route('admin.orders.show', $o->id) }}" class="invoice-number-wrap" title="Klik untuk kelola pesanan">
                                            {{ $o->order_number }}
                                        </a>
                                        <button type="button" class="invoice-copy-btn" onclick="copyInvoice('{{ $o->order_number }}', this)" title="Salin No. Invoice">
                                            📋
                                        </button>
                                    </div>
                                    <div class="text-xs text-muted" style="margin-top: 2px;">
                                        {{ $o->created_at->format('d M Y, H:i') }} WIB
                                    </div>

                                    @if($o->payment_proof)
                                        <div style="margin-top: 6px;">
                                            <button type="button" class="proof-mini-pill" 
                                                    onclick="openProofQuickModal('{{ $o->payment_proof_url }}', '{{ $o->order_number }}', '{{ $o->formatted_grand_total }}', '{{ $o->payment_method }}')">
                                                📷 Bukti Bayar
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- 2. Customer & Contact -->
                            <td>
                                <div class="customer-info-box">
                                    <div class="customer-avatar-circle">
                                        {{ strtoupper(substr($o->recipient_name, 0, 1)) }}
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="customer-name-text">
                                            {{ $o->recipient_name }}
                                        </div>
                                        <div class="customer-meta-sub">
                                            @php
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $o->recipient_phone);
                                                if(str_starts_with($cleanPhone, '0')) {
                                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                                }
                                            @endphp
                                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" style="color: #059669; font-weight: 600;" title="Chat via WhatsApp">
                                                💬 {{ $o->recipient_phone }}
                                            </a>
                                        </div>
                                        <div class="customer-meta-sub">
                                            <span>📍 {{ $o->city }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Ordered Books Items -->
                            <td>
                                <div class="order-items-preview">
                                    @php
                                        $firstItem = $o->items->first();
                                    @endphp

                                    @if($firstItem && $firstItem->book)
                                        <img src="{{ $firstItem->book->cover_url }}" alt="{{ $firstItem->book_title }}" class="order-items-thumb">
                                    @else
                                        <div class="order-items-thumb-placeholder">📖</div>
                                    @endif

                                    <div style="min-width: 0;">
                                        <div class="items-title-text" title="{{ $firstItem?->book_title ?? 'Item Buku' }}">
                                            {{ $firstItem?->book_title ?? 'Item Buku' }}
                                        </div>
                                        <div class="items-count-badge">
                                            {{ $firstItem?->quantity ?? 1 }} eks
                                            @if($o->items->count() > 1)
                                                • <strong style="color: var(--primary);">+{{ $o->items->count() - 1 }} buku lainnya</strong>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 4. Total & Payment Method -->
                            <td style="text-align: right;">
                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary); letter-spacing: -0.3px;">
                                    {{ $o->formatted_grand_total }}
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    <span style="font-weight: 600; color: var(--text-main); background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                        {{ $o->payment_method }}
                                    </span>
                                </div>
                                <div style="margin-top: 4px;">
                                    @if($o->stock_reduced)
                                        <span style="font-size: 0.72rem; color: #059669; font-weight: 700;">✓ Stok Berkurang</span>
                                    @else
                                        <span style="font-size: 0.72rem; color: #64748b;">Stok Belum Terpotong</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 5. Modern Status Badge -->
                            <td style="text-align: center;">
                                <span class="status-pill-modern status-{{ $o->status }}">
                                    <span class="status-dot"></span>
                                    <span>{{ $o->status_label }}</span>
                                </span>

                                @if($o->status === 'dikirim' && $o->tracking_number)
                                    <div class="text-xs text-muted" style="margin-top: 4px; font-family: monospace;">
                                        Resi: <strong>{{ Str::limit($o->tracking_number, 12) }}</strong>
                                    </div>
                                @endif
                            </td>

                            <!-- 6. Actions -->
                            <td style="text-align: right;">
                                <div class="d-flex align-center justify-end gap-2">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="btn-manage-order">
                                        <span>Kelola</span>
                                        <span>→</span>
                                    </a>

                                    <a href="{{ route('orders.invoice', $o->order_number) }}" class="action-btn" title="Lihat Invoice Cetak" target="_blank">
                                        🖨️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 48px 20px; text-align: center;">
                                <div style="font-size: 3.5rem; margin-bottom: 12px;">📦</div>
                                <h3 style="font-size: 1.25rem; margin-bottom: 6px; color: var(--text-main);">Tidak Ada Pesanan Ditemukan</h3>
                                <p class="text-muted text-sm" style="max-width: 440px; margin: 0 auto 18px auto;">
                                    Tidak ada data pesanan yang cocok dengan kriteria filter atau pencarian Anda saat ini.
                                </p>
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                                    Lihat Semua Pesanan
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modern Pagination Footer -->
<div class="d-flex align-center justify-between flex-wrap gap-3 mt-4">
    <div class="text-xs text-muted">
        Menampilkan <strong>{{ $orders->firstItem() ?? 0 }}</strong> sampai <strong>{{ $orders->lastItem() ?? 0 }}</strong> dari <strong>{{ $orders->total() }}</strong> total pesanan.
    </div>
    <div>
        {{ $orders->links() }}
    </div>
</div>

<!-- Quick Payment Proof Modal -->
<div class="modal-overlay" id="quickProofModal">
    <div class="modal-dialog" style="max-width: 580px;">
        <div class="modal-header">
            <h3 class="modal-title" id="proofModalInvoice">Bukti Transfer Pembayaran</h3>
            <button type="button" class="modal-close" onclick="closeQuickProofModal()">&times;</button>
        </div>
        <div class="modal-body text-center" style="padding: 20px;">
            <div id="proofMetaBox" style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem;">
                <div>
                    <span class="text-muted text-xs block font-bold uppercase">Metode</span>
                    <strong id="proofModalMethod">-</strong>
                </div>
                <div>
                    <span class="text-muted text-xs block font-bold uppercase">Total Tagihan</span>
                    <strong id="proofModalTotal" style="color: var(--primary); font-size: 1.05rem;">-</strong>
                </div>
            </div>

            <div style="background: #0f172a; border-radius: var(--radius-sm); padding: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <img id="quickProofImg" src="#" alt="Bukti Transfer" style="max-height: 440px; max-width: 100%; object-fit: contain; border-radius: 4px;">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeQuickProofModal()">Tutup</button>
            <a href="#" id="proofModalDetailBtn" class="btn btn-primary btn-sm">Buka Halaman Kelola Pesanan →</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyInvoice(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '✓';
        btn.style.color = 'var(--success)';
        setTimeout(() => {
            btn.innerHTML = original;
            btn.style.color = '';
        }, 1800);
    });
}

function openQuickProofModal(imgSrc, invoice, total, method) {
    document.getElementById('quickProofImg').src = imgSrc;
    document.getElementById('proofModalInvoice').innerText = 'Bukti Transfer #' + invoice;
    document.getElementById('proofModalTotal').innerText = total;
    document.getElementById('proofModalMethod').innerText = method;
    document.getElementById('quickProofModal').classList.add('active');
}

function closeQuickProofModal() {
    document.getElementById('quickProofModal').classList.remove('active');
}
</script>
@endpush
