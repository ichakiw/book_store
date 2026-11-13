@extends('layouts.app')

@section('title', 'Detail Pesanan ' . $order->order_number . ' - BookStore')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb & Top Actions -->
    <div class="d-flex align-center justify-between flex-wrap gap-3 mb-4">
        <div>
            <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 6px;">
                <a href="{{ route('books.index') }}">Katalog Buku</a> / <a href="{{ route('orders.index') }}">Pesanan Saya</a> / <span>{{ $order->order_number }}</span>
            </div>
            <h1 style="font-size: 1.85rem; margin-bottom: 4px;">Detail Pesanan #{{ $order->order_number }}</h1>
            <p class="text-muted text-sm">Dipesan pada tanggal {{ $order->created_at->format('d F Y, H:i') }} WIB</p>
        </div>

        <div class="d-flex align-center gap-2">
            <a href="{{ route('orders.invoice', $order->order_number) }}" class="btn btn-secondary btn-sm" target="_blank">
                🖨️ Cetak Invoice
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-outline btn-sm">
                ← Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Order Tracking Status Card -->
    <div class="order-tracking">
        <div class="d-flex align-center justify-between mb-4 flex-wrap gap-2">
            <div>
                <span class="text-xs text-muted font-bold uppercase">Status Terkini:</span>
                <div style="margin-top: 4px;">
                    <span class="badge {{ $order->status_badge_class }}" style="font-size: 0.95rem; padding: 6px 14px;">
                        {{ $order->status_label }}
                    </span>
                </div>
            </div>

            @if($order->status === 'menunggu_pembayaran' || $order->status === 'pembayaran_ditolak')
                <div>
                    <a href="{{ route('orders.payment', $order->order_number) }}" class="btn btn-primary btn-sm" style="box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);">
                        💳 Bayar &amp; Upload Bukti Transfer →
                    </a>
                </div>
            @endif

            @if($order->status === 'dikirim')
                <div>
                    <form action="{{ route('orders.confirm_receipt', $order->order_number) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa buku telah sampai di tangan Anda dengan selamat?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            ✓ Konfirmasi Pesanan Diterima
                        </button>
                    </form>
                </div>
            @endif

            @if($order->status === 'menunggu_pembayaran')
                <div>
                    <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            ✕ Batalkan Pesanan
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if($order->status === 'pembayaran_ditolak')
            <div class="alert alert-danger mb-4">
                <span style="font-size: 1.3rem;">⚠️</span>
                <div>
                    <strong>Pembayaran Anda Ditolak:</strong>
                    <div style="margin-top: 4px;">{{ $order->payment_note ?? 'Bukti transfer tidak valid atau jumlah nominal tidak sesuai.' }}</div>
                    <div style="margin-top: 8px;">
                        <a href="{{ route('orders.payment', $order->order_number) }}" class="btn btn-danger btn-sm">
                            Upload Ulang Bukti Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if($order->status === 'dibatalkan')
            <div class="alert alert-warning mb-4">
                <span>✕</span>
                <div>
                    <strong>Pesanan ini telah dibatalkan.</strong>
                    <div class="text-xs mt-1">Transaksi tidak dilanjutkan. Jika ada pertanyaan silakan hubungi tim dukungan kami.</div>
                </div>
            </div>
        @else
            <!-- Step Timeline Flow -->
            @php
                $steps = [
                    'menunggu_pembayaran' => ['num' => 1, 'title' => 'Menunggu Pembayaran', 'icon' => '1'],
                    'menunggu_konfirmasi' => ['num' => 2, 'title' => 'Menunggu Konfirmasi', 'icon' => '2'],
                    'diproses'            => ['num' => 3, 'title' => 'Diproses Admin', 'icon' => '3'],
                    'dikirim'             => ['num' => 4, 'title' => 'Dalam Pengiriman', 'icon' => '4'],
                    'selesai'             => ['num' => 5, 'title' => 'Pesanan Selesai', 'icon' => '5'],
                ];

                $currentStepNum = match($order->status) {
                    'menunggu_pembayaran' => 1,
                    'menunggu_konfirmasi' => 2,
                    'diproses'            => 3,
                    'dikirim'             => 4,
                    'selesai'             => 5,
                    default               => 1,
                };
            @endphp

            <div class="tracking-steps">
                @foreach($steps as $key => $s)
                    @php
                        $isCompleted = $s['num'] < $currentStepNum || $order->status === 'selesai';
                        $isActive = $s['num'] === $currentStepNum && $order->status !== 'selesai';
                    @endphp
                    <div class="tracking-step {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}">
                        <div class="step-icon-wrap">
                            @if($isCompleted)
                                ✓
                            @else
                                {{ $s['icon'] }}
                            @endif
                        </div>
                        <div class="step-title">{{ $s['title'] }}</div>
                        @if($key === 'dikirim' && $order->shipped_at)
                            <div class="step-date">{{ $order->shipped_at->format('d M, H:i') }}</div>
                        @elseif($key === 'selesai' && $order->completed_at)
                            <div class="step-date">{{ $order->completed_at->format('d M, H:i') }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 2 Column Details: Left Info, Right Summary -->
    <div class="d-grid" style="grid-template-columns: 2fr 1fr; gap: 24px; align-items: flex-start;">
        <div class="d-flex flex-column gap-4">
            <!-- Order Items Card -->
            <div class="card" style="border-radius: var(--radius-md);">
                <div class="card-header">
                    <h3 class="card-title">📖 Daftar Buku yang Dipesan</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th style="text-align: right;">Harga</th>
                                <th style="text-align: center;">Jumlah</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-center gap-3">
                                            <div style="width: 48px; height: 64px; background: #f8fafc; border: 1px solid var(--border); border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                                                📖
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: var(--text-main);">
                                                    {{ $item->book_title }}
                                                </div>
                                                <div class="text-xs text-muted">
                                                    Penulis: {{ $item->book_author ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right; font-weight: 600;">
                                        {{ $item->formatted_price }}
                                    </td>
                                    <td style="text-align: center; font-weight: 700;">
                                        {{ $item->quantity }}
                                    </td>
                                    <td style="text-align: right; font-weight: 800; color: var(--primary);">
                                        {{ $item->formatted_subtotal }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Shipping & Courier Card -->
            <div class="card" style="border-radius: var(--radius-md);">
                <div class="card-header">
                    <h3 class="card-title">🚚 Informasi Pengiriman &amp; Penerima</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <div class="text-xs text-muted font-bold uppercase mb-1">Nama Penerima</div>
                            <div style="font-weight: 700; font-size: 1rem; color: var(--text-main);">
                                {{ $order->recipient_name }}
                            </div>
                            <div class="text-sm text-muted">📞 {{ $order->recipient_phone }}</div>

                            <div class="text-xs text-muted font-bold uppercase mt-3 mb-1">Alamat Tujuan</div>
                            <div style="font-size: 0.92rem; line-height: 1.5; color: var(--text-main);">
                                {{ $order->shipping_address }}<br>
                                {{ $order->city }}, {{ $order->postal_code }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-muted font-bold uppercase mb-1">Status Pengiriman</div>
                            @if($order->tracking_number)
                                <div style="padding: 12px; background-color: var(--cyan-light); border: 1px solid var(--cyan-border); border-radius: var(--radius-sm);">
                                    <div style="font-size: 0.82rem; color: #0891b2; font-weight: 700; text-transform: uppercase;">Kurir / Ekspedisi</div>
                                    <div style="font-weight: 800; font-size: 1.05rem; color: #0e7490; margin-bottom: 4px;">
                                        {{ $order->courier_name ?? 'Kurir Toko' }}
                                    </div>
                                    <div style="font-size: 0.82rem; color: #0891b2; font-weight: 700; text-transform: uppercase;">Nomor Resi Pengiriman</div>
                                    <div style="font-family: monospace; font-size: 1.15rem; font-weight: 800; color: #083344; letter-spacing: 1px;">
                                        {{ $order->tracking_number }}
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        Dikirim pada: {{ $order->shipped_at ? $order->shipped_at->format('d M Y, H:i') : '-' }}
                                    </div>
                                </div>
                            @else
                                <div class="text-sm text-muted" style="padding: 14px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed var(--border);">
                                    Nomor resi dan kurir akan otomatis dicantumkan di sini setelah pesanan dikirimkan oleh admin toko.
                                </div>
                            @endif

                            @if($order->notes)
                                <div class="mt-3">
                                    <div class="text-xs text-muted font-bold uppercase mb-1">Catatan Pembeli</div>
                                    <div class="text-sm" style="font-style: italic; color: var(--secondary);">
                                        "{{ $order->notes }}"
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Payment & Price Summary -->
        <div class="d-flex flex-column gap-4">
            <!-- Payment Card -->
            <div class="card" style="border-radius: var(--radius-md);">
                <div class="card-header">
                    <h3 class="card-title">💳 Informasi Pembayaran</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-xs text-muted font-bold uppercase mb-1">Metode Pembayaran</div>
                        <div style="font-weight: 700; font-size: 0.98rem; color: var(--text-main);">
                            {{ $order->payment_method }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-xs text-muted font-bold uppercase mb-1">Bukti Transfer</div>
                        @if($order->payment_proof_url)
                            <div style="margin-top: 8px;">
                                <a href="{{ $order->payment_proof_url }}" target="_blank" style="display: block; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-xs);">
                                    <img src="{{ $order->payment_proof_url }}" alt="Bukti Pembayaran" style="max-height: 180px; width: 100%; object-fit: cover;">
                                </a>
                                <div class="text-xs text-muted text-center mt-1">Klik gambar untuk melihat ukuran penuh</div>
                            </div>
                        @else
                            <div style="padding: 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed var(--border); font-size: 0.85rem; color: var(--text-muted);">
                                Belum ada bukti pembayaran yang diunggah.
                            </div>
                        @endif
                    </div>

                    @if(in_array($order->status, ['menunggu_pembayaran', 'pembayaran_ditolak']))
                        <div class="mt-3">
                            <a href="{{ route('orders.payment', $order->order_number) }}" class="btn btn-primary btn-block btn-sm">
                                📤 Upload Bukti Pembayaran
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Price Breakdown Card -->
            <div class="card" style="border-radius: var(--radius-md);">
                <div class="card-header">
                    <h3 class="card-title">💰 Rincian Biaya</h3>
                </div>
                <div class="card-body">
                    <div class="summary-row">
                        <span>Subtotal Buku</span>
                        <strong style="color: var(--text-main);">{{ $order->formatted_total_amount }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Biaya Pengiriman</span>
                        <strong style="color: var(--text-main);">{{ $order->formatted_shipping_cost }}</strong>
                    </div>

                    <div class="summary-total" style="padding-top: 10px;">
                        <span>Total Tagihan</span>
                        <span style="color: var(--primary);">{{ $order->formatted_grand_total }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
