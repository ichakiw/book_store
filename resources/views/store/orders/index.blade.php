@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya - BookStore')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h1 style="font-size: 2rem; margin-bottom: 4px;">Riwayat Pembelian &amp; Status Pesanan</h1>
        <p class="text-muted text-sm">Pantau proses pengiriman dan riwayat seluruh transaksi buku Anda.</p>
    </div>

    <!-- Status Tabs Navigation -->
    <div class="category-pills mb-4">
        <a href="{{ route('orders.index') }}" class="category-pill {{ !$status ? 'active' : '' }}">
            Semua ({{ $statusCounts['all'] }})
        </a>
        <a href="{{ route('orders.index', ['status' => 'menunggu_pembayaran']) }}" class="category-pill {{ $status === 'menunggu_pembayaran' ? 'active' : '' }}">
            Menunggu Pembayaran ({{ $statusCounts['menunggu_pembayaran'] }})
        </a>
        <a href="{{ route('orders.index', ['status' => 'menunggu_konfirmasi']) }}" class="category-pill {{ $status === 'menunggu_konfirmasi' ? 'active' : '' }}">
            Menunggu Konfirmasi ({{ $statusCounts['menunggu_konfirmasi'] }})
        </a>
        <a href="{{ route('orders.index', ['status' => 'diproses']) }}" class="category-pill {{ $status === 'diproses' ? 'active' : '' }}">
            Diproses ({{ $statusCounts['diproses'] }})
        </a>
        <a href="{{ route('orders.index', ['status' => 'dikirim']) }}" class="category-pill {{ $status === 'dikirim' ? 'active' : '' }}">
            Dikirim ({{ $statusCounts['dikirim'] }})
        </a>
        <a href="{{ route('orders.index', ['status' => 'selesai']) }}" class="category-pill {{ $status === 'selesai' ? 'active' : '' }}">
            Selesai ({{ $statusCounts['selesai'] }})
        </a>
    </div>

    @if($orders->count() > 0)
        <div class="d-flex flex-column gap-3">
            @foreach($orders as $order)
                <div class="card" style="border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                    <div class="card-header d-flex justify-between align-center flex-wrap gap-2" style="background-color: #f8fafc;">
                        <div class="d-flex align-center gap-3">
                            <span style="font-size: 1.1rem;">📦</span>
                            <div>
                                <span style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                    {{ $order->order_number }}
                                </span>
                                <span class="text-muted text-xs" style="margin-left: 8px;">
                                    {{ $order->created_at->format('d M Y, H:i') }} WIB
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="badge {{ $order->status_badge_class }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Order items snapshot -->
                        <div class="d-flex flex-column gap-3 mb-3">
                            @foreach($order->items as $item)
                                <div class="d-flex align-center justify-between gap-3">
                                    <div class="d-flex align-center gap-3" style="flex: 1; min-width: 0;">
                                        <div style="width: 44px; height: 58px; background: #f1f5f9; border-radius: 4px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                            📖
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="font-weight: 700; font-size: 0.94rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $item->book_title }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $item->quantity }} barang x {{ $item->formatted_price }}
                                            </div>
                                        </div>
                                    </div>

                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                        {{ $item->formatted_subtotal }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tracking / Courier info if shipped -->
                        @if($order->status === 'dikirim' && $order->tracking_number)
                            <div style="padding: 10px 14px; background-color: var(--cyan-light); border: 1px solid var(--cyan-border); border-radius: var(--radius-sm); margin-bottom: 14px; font-size: 0.85rem; color: #0e7490;">
                                🚚 Dikirim dengan <strong>{{ $order->courier_name }}</strong> • Nomor Resi: <strong>{{ $order->tracking_number }}</strong>
                            </div>
                        @endif

                        <!-- Payment rejection notice if rejected -->
                        @if($order->status === 'pembayaran_ditolak')
                            <div class="alert alert-danger" style="margin-bottom: 14px; padding: 10px 14px; font-size: 0.85rem;">
                                ⚠️ <strong>Pembayaran Ditolak:</strong> {{ $order->payment_note ?? 'Bukti pembayaran tidak sah atau kurang.' }}
                            </div>
                        @endif

                        <!-- Footer: Total & Actions -->
                        <div class="d-flex align-center justify-between flex-wrap gap-3 pt-3" style="border-top: 1px solid var(--border-light);">
                            <div>
                                <span class="text-xs text-muted">Total Pembayaran:</span>
                                <div style="font-size: 1.2rem; font-weight: 800; color: var(--primary);">
                                    {{ $order->formatted_grand_total }}
                                </div>
                            </div>

                            <div class="d-flex align-center gap-2 flex-wrap">
                                <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-secondary btn-sm">
                                    Lihat Detail &amp; Lacak
                                </a>

                                @if($order->status === 'menunggu_pembayaran' || $order->status === 'pembayaran_ditolak')
                                    <a href="{{ route('orders.payment', $order->order_number) }}" class="btn btn-primary btn-sm">
                                        💳 Bayar / Upload Bukti
                                    </a>
                                @endif

                                @if($order->status === 'dikirim')
                                    <form action="{{ route('orders.confirm_receipt', $order->order_number) }}" method="POST" onsubmit="return confirm('Apakah buku pesanan Anda sudah diterima dengan baik?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            ✓ Pesanan Diterima
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-center">
            {{ $orders->links() }}
        </div>
    @else
        <div class="card text-center py-5" style="border-radius: var(--radius-md);">
            <div class="card-body">
                <div style="font-size: 3.5rem; margin-bottom: 12px;">📦</div>
                <h3 style="font-size: 1.3rem; margin-bottom: 6px;">Belum Ada Pesanan</h3>
                <p class="text-muted text-sm" style="max-width: 400px; margin: 0 auto 20px auto;">
                    Tidak ditemukan riwayat pesanan dengan status yang dipilih.
                </p>
                <a href="{{ route('books.index') }}" class="btn btn-primary">
                    Mulai Belanja Buku Sekarang
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
