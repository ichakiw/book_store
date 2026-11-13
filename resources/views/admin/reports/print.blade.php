<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan ({{ $startDate }} s/d {{ $endDate }}) - BookStore</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { background: #ffffff; padding: 30px; font-size: 13px; color: #000; }
        .print-container { max-width: 960px; margin: 0 auto; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Controls -->
        <div class="d-flex justify-between align-center mb-4 pb-3 no-print" style="border-bottom: 1px solid #ccc;">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">← Kembali</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Cetak Dokumen</button>
        </div>

        <!-- Header -->
        <div class="d-flex justify-between align-center mb-4 pb-3" style="border-bottom: 2px solid #333;">
            <div>
                <h1 style="font-size: 1.8rem; margin-bottom: 2px;">LAPORAN PENJUALAN TOKO BUKU</h1>
                <div style="font-weight: 700; color: #555;">PT BOOK STORE INDONESIA</div>
                <div class="text-xs text-muted">Dicetak pada: {{ date('d F Y, H:i') }} WIB</div>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: 700;">Periode Laporan:</div>
                <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary);">
                    {{ date('d/m/Y', strtotime($startDate)) }} — {{ date('d/m/Y', strtotime($endDate)) }}
                </div>
                <div class="text-xs text-muted">Status: {{ strtoupper($status) }}</div>
            </div>
        </div>

        <!-- Metric Summary Table -->
        <table class="table mb-4" style="border: 1px solid #ccc;">
            <tr style="background: #f8fafc;">
                <td style="padding: 12px; text-align: center;">
                    <div class="text-xs text-muted font-bold uppercase">Total Omset</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: #16a34a;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </td>
                <td style="padding: 12px; text-align: center; border-left: 1px solid #ccc;">
                    <div class="text-xs text-muted font-bold uppercase">Total Transaksi</div>
                    <div style="font-size: 1.35rem; font-weight: 800;">{{ $totalOrders }}</div>
                </td>
                <td style="padding: 12px; text-align: center; border-left: 1px solid #ccc;">
                    <div class="text-xs text-muted font-bold uppercase">Buku Terjual</div>
                    <div style="font-size: 1.35rem; font-weight: 800;">{{ $totalBooksSold }} eks</div>
                </td>
                <td style="padding: 12px; text-align: center; border-left: 1px solid #ccc;">
                    <div class="text-xs text-muted font-bold uppercase">Rata-rata Transaksi</div>
                    <div style="font-size: 1.35rem; font-weight: 800;">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>

        <!-- Transactions List -->
        <table class="table" style="border: 1px solid #ccc;">
            <thead>
                <tr style="background: #eee;">
                    <th style="width: 40px;">No</th>
                    <th>No. Invoice</th>
                    <th>Tanggal</th>
                    <th>Nama Pembeli</th>
                    <th style="text-align: right;">Subtotal</th>
                    <th style="text-align: right;">Ongkir</th>
                    <th style="text-align: right;">Grand Total</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $idx => $o)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="font-weight: 700;">{{ $o->order_number }}</td>
                        <td>{{ $o->created_at->format('d/m/Y') }}</td>
                        <td>{{ $o->recipient_name }}</td>
                        <td style="text-align: right;">{{ $o->formatted_total_amount }}</td>
                        <td style="text-align: right;">{{ $o->formatted_shipping_cost }}</td>
                        <td style="text-align: right; font-weight: 700;">{{ $o->formatted_grand_total }}</td>
                        <td style="text-align: center;">{{ strtoupper($o->status_label) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td colspan="6" style="text-align: right; padding: 12px;">TOTAL PENJUALAN KESELURUHAN:</td>
                    <td style="text-align: right; padding: 12px; font-size: 1.1rem; color: #16a34a;">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Footer -->
        <div class="d-flex justify-between mt-5 pt-4" style="margin-top: 60px;">
            <div style="text-align: center; width: 200px;">
                <div class="text-xs text-muted">Dibuat Oleh:</div>
                <div style="margin-top: 50px; font-weight: 700; border-top: 1px solid #000; padding-top: 4px;">
                    Staff Administrasi Toko
                </div>
            </div>

            <div style="text-align: center; width: 200px;">
                <div class="text-xs text-muted">Disetujui Oleh:</div>
                <div style="margin-top: 50px; font-weight: 700; border-top: 1px solid #000; padding-top: 4px;">
                    Manager Operasional
                </div>
            </div>
        </div>
    </div>
</body>
</html>
