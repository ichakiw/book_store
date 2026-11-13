<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - BookStore</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { background-color: #ffffff; padding: 40px 20px; font-size: 14px; }
        .invoice-card { max-width: 800px; margin: 0 auto; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 36px; }
        @media print {
            body { padding: 0; }
            .invoice-card { border: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-card">
        <!-- Print Header & Controls -->
        <div class="d-flex align-center justify-between mb-4 no-print" style="border-bottom: 1px solid var(--border); padding-bottom: 16px;">
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary btn-sm">
                    ← Kembali ke Detail Pesanan (Admin)
                </a>
            @elseif(auth()->check())
                <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-secondary btn-sm">
                    ← Kembali ke Pesanan
                </a>
            @else
                <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
                    ← Kembali ke Beranda
                </a>
            @endif
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                🖨️ Cetak / Unduh PDF
            </button>
        </div>

        <!-- Invoice Brand & Header -->
        <div class="d-flex align-center justify-between mb-4 pb-4" style="border-bottom: 2px solid var(--border);">
            <div>
                <div class="d-flex align-center gap-2" style="font-size: 1.6rem; font-weight: 800; color: var(--primary);">
                    <span>📚</span> BookStore
                </div>
                <div class="text-xs text-muted mt-1">PT BOOK STORE INDONESIA</div>
                <div class="text-xs text-muted">Jl. Jenderal Sudirman Kav. 45, Jakarta Pusat</div>
                <div class="text-xs text-muted">support@bookstore.id • 0812-3456-7890</div>
            </div>

            <div style="text-align: right;">
                <h1 style="font-size: 1.8rem; color: var(--text-main); margin-bottom: 4px;">INVOICE</h1>
                <div style="font-family: monospace; font-weight: 800; font-size: 1.1rem; color: var(--primary);">
                    {{ $order->order_number }}
                </div>
                <div class="text-xs text-muted mt-1">Tanggal: {{ $order->created_at->format('d/m/Y H:i') }} WIB</div>
                <div style="margin-top: 6px;">
                    <span class="badge {{ $order->status_badge_class }}">
                        {{ strtoupper($order->status_label) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Recipient & Payment Details -->
        <div class="d-grid mb-4" style="grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <div class="text-xs text-muted font-bold uppercase mb-1">DITUJUKAN KEPADA:</div>
                <div style="font-weight: 700; font-size: 1rem; color: var(--text-main);">{{ $order->recipient_name }}</div>
                <div class="text-sm text-muted">📞 {{ $order->recipient_phone }}</div>
                <div class="text-sm mt-1" style="line-height: 1.5;">
                    {{ $order->shipping_address }}<br>
                    {{ $order->city }}, {{ $order->postal_code }}
                </div>
            </div>

            <div>
                <div class="text-xs text-muted font-bold uppercase mb-1">INFORMASI TRANSAKSI:</div>
                <div class="text-sm"><strong>Metode Pembayaran:</strong> {{ $order->payment_method }}</div>
                <div class="text-sm mt-1"><strong>Status Pembayaran:</strong> {{ in_array($order->status, ['diproses', 'dikirim', 'selesai']) ? 'Lunas / Terverifikasi' : 'Menunggu Konfirmasi' }}</div>
                @if($order->tracking_number)
                    <div class="text-sm mt-1"><strong>Ekspedisi / Kurir:</strong> {{ $order->courier_name }} (Resi: {{ $order->tracking_number }})</div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="table mb-4" style="border: 1px solid var(--border);">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th style="width: 50px;">No</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="font-weight: 700;">{{ $item->book_title }}</td>
                        <td class="text-muted">{{ $item->book_author ?? '-' }}</td>
                        <td style="text-align: right;">{{ $item->formatted_price }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $item->quantity }}</td>
                        <td style="text-align: right; font-weight: 700;">{{ $item->formatted_subtotal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Breakdown -->
        <div class="d-flex justify-end mb-4">
            <div style="width: 300px;">
                <div class="d-flex justify-between py-1 text-sm">
                    <span class="text-muted">Subtotal Produk:</span>
                    <strong>{{ $order->formatted_total_amount }}</strong>
                </div>
                <div class="d-flex justify-between py-1 text-sm">
                    <span class="text-muted">Biaya Pengiriman:</span>
                    <strong>{{ $order->formatted_shipping_cost }}</strong>
                </div>
                <div class="d-flex justify-between pt-2 mt-2" style="border-top: 2px solid var(--text-main); font-size: 1.15rem; font-weight: 800;">
                    <span>Total Pembayaran:</span>
                    <span style="color: var(--primary);">{{ $order->formatted_grand_total }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center pt-4" style="border-top: 1px solid var(--border); font-size: 0.8rem; color: var(--text-muted);">
            Terima kasih telah berbelanja di <strong>BookStore</strong>. Simpan invoice ini sebagai bukti pembelian resmi.
        </div>
    </div>
</body>
</html>
