@extends('layouts.admin')

@section('title', 'Kelola Pesanan ' . $order->order_number . ' - BookStore Admin')
@section('page_title', 'Kelola Pesanan: #' . $order->order_number)

@section('content')
<!-- Top Actions & Navigation -->
<div class="mb-4 d-flex align-center justify-between flex-wrap gap-3">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
            ← Kembali ke Daftar Pesanan
        </a>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('orders.invoice', $order->order_number) }}" class="btn btn-outline btn-sm" target="_blank">
            🖨️ Cetak Invoice Pesanan
        </a>
    </div>
</div>

<!-- Modern Order Status & Tracking Header Banner -->
<div class="card mb-4" style="border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex align-center justify-between flex-wrap gap-3 pb-3" style="border-bottom: 1px solid var(--border-light);">
            <div class="d-flex align-center gap-3">
                <div class="orders-kpi-icon" style="background: var(--primary-light); color: var(--primary); width: 50px; height: 50px; font-size: 1.5rem;">
                    📦
                </div>
                <div>
                    <div class="d-flex align-center gap-2">
                        <span style="font-family: monospace; font-size: 1.25rem; font-weight: 800; color: var(--text-main);">
                            {{ $order->order_number }}
                        </span>
                        <button type="button" class="invoice-copy-btn" onclick="navigator.clipboard.writeText('{{ $order->order_number }}'); this.innerText='✓'; setTimeout(()=>this.innerText='📋', 1500);" title="Salin Invoice">
                            📋
                        </button>
                    </div>
                    <div class="text-xs text-muted mt-1">
                        Dibuat pada {{ $order->created_at->format('d F Y, H:i') }} WIB ({{ $order->created_at->diffForHumans() }})
                    </div>
                </div>
            </div>

            <div class="d-flex align-center gap-2 flex-wrap">
                <span class="status-pill-modern status-{{ $order->status }}" style="font-size: 0.88rem; padding: 6px 16px;">
                    <span class="status-dot"></span>
                    <span>{{ $order->status_label }}</span>
                </span>

                @if($order->stock_reduced)
                    <span class="badge badge-success" style="font-size: 0.8rem; padding: 6px 12px;">
                        ✓ Stok Terpotong
                    </span>
                @else
                    <span class="badge badge-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                        ⏳ Stok Belum Dipotong
                    </span>
                @endif
            </div>
        </div>

        <!-- Visual Flow Tracker Steps -->
        @php
            $steps = [
                'menunggu_pembayaran' => ['num' => 1, 'title' => 'Menunggu Bayar', 'icon' => '1'],
                'menunggu_konfirmasi' => ['num' => 2, 'title' => 'Perlu Konfirmasi', 'icon' => '2'],
                'diproses'            => ['num' => 3, 'title' => 'Diproses Gudang', 'icon' => '3'],
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

        @if(!in_array($order->status, ['dibatalkan', 'pembayaran_ditolak']))
            <div class="tracking-steps mt-4" style="margin-bottom: 0;">
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
        @else
            <div class="mt-3">
                @if($order->status === 'pembayaran_ditolak')
                    <div class="alert alert-danger" style="margin-bottom: 0;">
                        <span>⚠️</span>
                        <div>
                            <strong>Pembayaran Pesanan Ditolak:</strong>
                            <div>{{ $order->payment_note ?? 'Bukti transfer tidak sesuai atau tidak sah.' }}</div>
                        </div>
                    </div>
                @elseif($order->status === 'dibatalkan')
                    <div class="alert alert-secondary" style="margin-bottom: 0;">
                        <span>🚫</span>
                        <div>
                            <strong>Pesanan Dibatalkan:</strong>
                            <div>Transaksi ini telah dibatalkan dan stok buku telah dikembalikan ke katalog.</div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Main 2-Column Control Center -->
<div class="d-grid" style="grid-template-columns: 2fr 1.2fr; gap: 24px; align-items: flex-start;">
    <!-- Left Column: Actions & Control Panels -->
    <div class="d-flex flex-column gap-4">

        <!-- 1. VERIFIKASI & KONFIRMASI PEMBAYARAN -->
        <div class="card" style="border-radius: var(--radius-md); border-color: {{ in_array($order->status, ['menunggu_konfirmasi', 'menunggu_pembayaran']) ? 'var(--warning)' : 'var(--border)' }};">
            <div class="card-header d-flex justify-between align-center">
                <h3 class="card-title">💳 1. Verifikasi Pembayaran Transaksi</h3>
                <span class="badge {{ $order->paid_at ? 'badge-success' : 'badge-warning' }}">
                    {{ $order->paid_at ? 'Bukti Diupload' : 'Belum Ada Bukti' }}
                </span>
            </div>
            <div class="card-body">
                <div class="d-grid mb-3" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <div class="text-xs text-muted font-bold uppercase">Metode Pembayaran</div>
                        <div style="font-weight: 700; font-size: 1.05rem; color: var(--text-main);">{{ $order->payment_method }}</div>
                        <div class="text-xs text-muted mt-1">Total Tagihan: <strong style="color: var(--primary); font-size: 1rem;">{{ $order->formatted_grand_total }}</strong></div>
                    </div>
                    <div>
                        <div class="text-xs text-muted font-bold uppercase">Waktu Pembayaran User</div>
                        <div style="font-weight: 600; color: var(--text-main);">
                            {{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i') . ' WIB' : 'Belum ada waktu pembayaran' }}
                        </div>
                    </div>
                </div>

                <!-- Bukti Transfer Preview Box -->
                <div class="mb-4">
                    <div class="text-xs text-muted font-bold uppercase mb-2">Foto Bukti Transfer Pembeli:</div>
                    @if($order->payment_proof_url)
                        <div style="display: flex; align-items: center; gap: 16px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 14px;">
                            <img src="{{ $order->payment_proof_url }}" alt="Bukti Transfer" 
                                 onclick="openProofModal('{{ $order->payment_proof_url }}')" 
                                 style="max-height: 120px; width: 100px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); cursor: pointer; box-shadow: var(--shadow-sm);">
                            <div>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">Foto Bukti Transfer Tersedia</div>
                                <div class="text-xs text-muted mt-1">Pastikan nama pengirim dan nominal tepat Rp {{ number_format($order->grand_total, 0, ',', '.') }}</div>
                                <button type="button" class="btn btn-outline btn-sm mt-2" onclick="openProofModal('{{ $order->payment_proof_url }}')">
                                    🔍 Perbesar Foto Bukti
                                </button>
                            </div>
                        </div>
                    @else
                        <div style="padding: 16px; background: #f8fafc; border: 1px dashed var(--border); border-radius: var(--radius-sm); color: var(--text-muted); font-size: 0.88rem; text-align: center;">
                            Pembeli belum mengunggah foto bukti pembayaran.
                        </div>
                    @endif
                </div>

                @if($order->payment_note)
                    <div class="alert alert-danger mb-3" style="font-size: 0.88rem;">
                        <strong>Alasan Penolakan Terakhir:</strong><br>
                        {{ $order->payment_note }}
                    </div>
                @endif

                <!-- Payment Action Buttons -->
                @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_pembayaran', 'pembayaran_ditolak']))
                    <div class="d-flex align-center gap-3 pt-3" style="border-top: 1px solid var(--border-light);">
                        <form action="{{ route('admin.orders.confirm_payment', $order->id) }}" method="POST" 
                              onsubmit="return confirm('Konfirmasi pembayaran pesanan ini? Stok buku akan otomatis dikurangi dari database dan status diubah menjadi Diproses.');" 
                              style="margin: 0; flex: 1;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block btn-lg" style="box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                                ✓ Konfirmasi Pembayaran (Setujui &amp; Kurangi Stok)
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger btn-lg" onclick="openRejectModal()">
                            ✕ Tolak Pembayaran
                        </button>
                    </div>
                @else
                    <div class="d-flex align-center gap-2" style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: var(--radius-sm); color: #065f46; font-size: 0.88rem; font-weight: 600;">
                        <span>✓</span> Pembayaran telah diverifikasi sah dan stok buku telah otomatis terpotong.
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. KELOLA PENGIRIMAN & NOMOR RESI -->
        <div class="card" style="border-radius: var(--radius-md); border-color: {{ $order->status === 'diproses' ? 'var(--cyan)' : 'var(--border)' }};">
            <div class="card-header d-flex justify-between align-center">
                <h3 class="card-title">🚚 2. Pengiriman &amp; Nomor Resi</h3>
                @if(in_array($order->status, ['dikirim', 'selesai']))
                    <span class="badge badge-cyan">Sudah Dikirim</span>
                @else
                    <span class="badge badge-secondary">Belum Dikirim</span>
                @endif
            </div>
            <div class="card-body">
                @if($order->tracking_number)
                    <div style="padding: 16px; background-color: var(--cyan-light); border: 1px solid var(--cyan-border); border-radius: var(--radius-sm); margin-bottom: 20px;">
                        <div class="d-flex justify-between align-center">
                            <div>
                                <div class="text-xs font-bold uppercase" style="color: #0891b2;">Ekspedisi Kurir</div>
                                <div style="font-size: 1.15rem; font-weight: 800; color: #0e7490;">
                                    {{ $order->courier_name }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="text-xs font-bold uppercase" style="color: #0891b2;">Nomor Resi Pengiriman</div>
                                <div class="d-flex align-center gap-2 justify-end">
                                    <span style="font-family: monospace; font-size: 1.25rem; font-weight: 800; color: #083344; letter-spacing: 1px;">
                                        {{ $order->tracking_number }}
                                    </span>
                                    <button type="button" class="invoice-copy-btn" onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}'); this.innerText='✓'; setTimeout(()=>this.innerText='📋', 1500);" title="Salin Resi">
                                        📋
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-muted mt-2 pt-2" style="border-top: 1px dashed var(--cyan-border);">
                            Waktu Pengiriman: {{ $order->shipped_at ? $order->shipped_at->format('d F Y, H:i') . ' WIB' : '-' }}
                        </div>
                    </div>
                @endif

                @if(in_array($order->status, ['diproses', 'dikirim']))
                    <form action="{{ route('admin.orders.update_shipping', $order->id) }}" method="POST">
                        @csrf
                        <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label required">Nama Ekspedisi / Kurir</label>
                                <input type="text" name="courier_name" value="{{ old('courier_name', $order->courier_name ?? 'JNE Express (Reguler)') }}" class="form-control" placeholder="Contoh: JNE / SiCepat / J&T" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Nomor Resi Pengiriman</label>
                                <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" class="form-control" placeholder="Contoh: JNE88291029381" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            🚚 {{ $order->status === 'dikirim' ? 'Perbarui Nomor Resi Pengiriman' : 'Simpan Resi & Ubah Status Menjadi Dikirim' }}
                        </button>
                    </form>
                @elseif($order->status === 'menunggu_pembayaran' || $order->status === 'menunggu_konfirmasi')
                    <div class="text-muted text-sm" style="padding: 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed var(--border);">
                        ⏳ <em>Konfirmasi pembayaran terlebih dahulu sebelum memasukkan nomor resi pengiriman.</em>
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. ACTIONS STATUS: SELESAIKAN ATAU BATALKAN -->
        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-header">
                <h3 class="card-title">⚙️ 3. Tindakan Status Akhir</h3>
            </div>
            <div class="card-body d-flex align-center gap-3 flex-wrap">
                @if($order->status === 'dikirim')
                    <form action="{{ route('admin.orders.complete', $order->id) }}" method="POST" onsubmit="return confirm('Tandai pesanan ini sebagai Selesai?');">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            ✓ Tandai Pesanan Selesai
                        </button>
                    </form>
                @endif

                @if($order->status !== 'selesai' && $order->status !== 'dibatalkan')
                    <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Jika stok sebelumnya sudah dikurangi, sistem akan otomatis mengembalikan stok buku.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            ✕ Batalkan Pesanan (Otomatis Kembalikan Stok)
                        </button>
                    </form>
                @endif

                @if($order->status === 'selesai')
                    <div class="text-sm font-bold text-success">
                        ✓ Transaksi telah selesai secara keseluruhan pada {{ $order->completed_at ? $order->completed_at->format('d/m/Y H:i') : '' }} WIB.
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Right Column: Customer Info & Order Items Breakdown -->
    <div class="d-flex flex-column gap-4">

        <!-- Customer & Delivery Card -->
        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-header">
                <h3 class="card-title">👤 Informasi Penerima &amp; Kontak</h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-center gap-3 mb-3">
                    <div class="customer-avatar-circle" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        {{ strtoupper(substr($order->recipient_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 1.05rem; color: var(--text-main);">{{ $order->recipient_name }}</div>
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $order->recipient_phone);
                            if(str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" style="color: #059669; font-weight: 600; font-size: 0.88rem;">
                            💬 Hubungi via WhatsApp ({{ $order->recipient_phone }})
                        </a>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-xs text-muted font-bold uppercase">Akun Terdaftar</div>
                    <div class="text-sm">
                        <a href="{{ route('admin.customers.show', $order->user_id) }}" style="font-weight: 700;">
                            {{ $order->user->name }}
                        </a>
                        <div class="text-xs text-muted">{{ $order->user->email }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-xs text-muted font-bold uppercase">Alamat Tujuan Pengiriman</div>
                    <div style="font-size: 0.92rem; line-height: 1.5; color: var(--text-main); margin-top: 2px; background: #f8fafc; padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        {{ $order->shipping_address }}<br>
                        <strong>{{ $order->city }}</strong>, {{ $order->postal_code }}
                    </div>
                </div>

                @if($order->notes)
                    <div style="padding: 10px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; font-size: 0.85rem; color: #92400e;">
                        <strong>Catatan Pembeli:</strong> <em>"{{ $order->notes }}"</em>
                    </div>
                @endif
            </div>
        </div>

        <!-- Ordered Items Summary -->
        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-header d-flex justify-between align-center">
                <h3 class="card-title">📖 Buku yang Dipesan</h3>
                <span class="badge badge-primary">{{ $order->items->sum('quantity') }} eks</span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3 mb-3">
                    @foreach($order->items as $item)
                        <div class="d-flex align-center justify-between gap-2" style="font-size: 0.92rem; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                            <div class="d-flex align-center gap-2" style="flex: 1; min-width: 0;">
                                @if($item->book)
                                    <img src="{{ $item->book->cover_url }}" alt="{{ $item->book_title }}" style="width: 34px; height: 46px; object-fit: cover; border-radius: 3px; border: 1px solid var(--border); flex-shrink: 0;">
                                @else
                                    <div style="width: 34px; height: 46px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 1rem; border-radius: 3px; border: 1px solid var(--border); flex-shrink: 0;">📖</div>
                                @endif
                                <div style="min-width: 0;">
                                    <div style="font-weight: 700; color: var(--text-main); line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->book_title }}">
                                        {{ $item->book_title }}
                                    </div>
                                    <div class="text-xs text-muted">
                                        {{ $item->quantity }} eks x {{ $item->formatted_price }}
                                    </div>
                                </div>
                            </div>
                            <div style="font-weight: 700; color: var(--text-main); flex-shrink: 0;">
                                {{ $item->formatted_subtotal }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="summary-row">
                    <span>Subtotal Produk</span>
                    <strong>{{ $order->formatted_total_amount }}</strong>
                </div>

                <div class="summary-row">
                    <span>Biaya Pengiriman</span>
                    <strong>{{ $order->formatted_shipping_cost }}</strong>
                </div>

                <div class="summary-total" style="padding-top: 10px;">
                    <span>Total Tagihan</span>
                    <span style="color: var(--primary);">{{ $order->formatted_grand_total }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Zoom Payment Proof -->
<div class="modal-overlay" id="proofModalOverlay">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-header">
            <h3 class="modal-title">Bukti Transfer Pembayaran</h3>
            <button type="button" class="modal-close" onclick="closeProofModal()">&times;</button>
        </div>
        <div class="modal-body text-center" style="background: #0f172a; padding: 12px; border-radius: var(--radius-sm); margin: 16px;">
            <img id="modalProofImg" src="#" alt="Proof" style="max-height: 500px; width: auto; max-width: 100%; object-fit: contain; border-radius: 4px;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeProofModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal: Reject Payment -->
<div class="modal-overlay" id="rejectModalOverlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title" style="color: var(--danger);">✕ Tolak Bukti Pembayaran</h3>
            <button type="button" class="modal-close" onclick="closeRejectModal()">&times;</button>
        </div>
        <form action="{{ route('admin.orders.reject_payment', $order->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Alasan Penolakan</label>
                    <textarea name="reason" rows="4" class="form-control" placeholder="Contoh: Nominal transfer kurang dari total tagihan / Bukti transfer buram tidak terbaca." required></textarea>
                    <div class="form-hint">Alasan ini akan ditampilkan kepada pembeli agar mereka dapat melakukan perbaikan atau upload ulang.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openProofModal(src) {
    document.getElementById('modalProofImg').src = src;
    document.getElementById('proofModalOverlay').classList.add('active');
}
function closeProofModal() {
    document.getElementById('proofModalOverlay').classList.remove('active');
}
function openRejectModal() {
    document.getElementById('rejectModalOverlay').classList.add('active');
}
function closeRejectModal() {
    document.getElementById('rejectModalOverlay').classList.remove('active');
}
</script>
@endpush
