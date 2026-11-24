@extends('layouts.app')

@section('title', 'Pembayaran Pesanan ' . $order->order_number . ' - BookStore')

@section('content')
<div class="container py-4">
    <div style="max-width: 760px; margin: 0 auto;">
        <!-- Header -->
        <div class="text-center mb-4">
            <span class="badge badge-warning" style="font-size: 0.85rem; padding: 6px 14px; margin-bottom: 8px;">
                MENUNGGU PEMBAYARAN
            </span>
            <h1 style="font-size: 2rem; margin-bottom: 6px;">Instruksi Pembayaran</h1>
            <p class="text-muted text-sm">Pesanan <strong>#{{ $order->order_number }}</strong> • Metode: <span class="badge {{ $order->isQris() ? 'badge-warning' : 'badge-primary' }}">{{ $order->payment_method }}</span></p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <div>
                    @foreach($errors->all() as $err)
                        <div>• {{ $err }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($order->status === 'pembayaran_ditolak')
            <div class="alert alert-danger mb-4">
                <span style="font-size: 1.3rem;">⚠️</span>
                <div>
                    <strong>Pembayaran Sebelumnya Ditolak Admin:</strong>
                    <div>{{ $order->payment_note ?? 'Bukti pembayaran tidak sah atau kurang.' }}</div>
                    <div class="text-xs mt-1">Silakan upload ulang bukti pembayaran yang benar di bawah ini.</div>
                </div>
            </div>
        @endif

        <!-- Total Tagihan Box -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff; border: none; border-radius: var(--radius-lg); box-shadow: 0 10px 25px rgba(37,99,235,0.3);">
            <div class="card-body text-center" style="padding: 32px 24px;">
                <div style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-bottom: 6px;">
                    Total yang Harus Dibayar
                </div>
                <div style="font-size: 2.6rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 12px;">
                    {{ $order->formatted_grand_total }}
                </div>
                <div class="d-inline-flex align-center gap-2" style="background: rgba(255,255,255,0.18); padding: 6px 16px; border-radius: var(--radius-pill); font-size: 0.82rem;">
                    <span>⚠️</span> Harap bayar tepat sesuai nominal di atas agar pesanan dapat diverifikasi oleh admin.
                </div>
            </div>
        </div>

        @if($order->isQris())
            <!-- QRIS PAYMENT BOX (KHUSUS QRIS - KODE QR BUKAN NOMOR TRANSFER) -->
            <div class="card mb-4" style="border-radius: var(--radius-md); overflow: hidden; border: 2px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0,0,0,0.06);">
                <!-- Official Red QRIS Header Banner -->
                <div style="background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%); color: #ffffff; padding: 18px 24px;">
                    <div class="d-flex align-center justify-between flex-wrap gap-2">
                        <div class="d-flex align-center gap-3">
                            <div style="background: #ffffff; color: #dc2626; font-weight: 900; font-size: 1.35rem; padding: 4px 14px; border-radius: 6px; letter-spacing: 1.5px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                QRIS
                            </div>
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; letter-spacing: 0.5px;">PEMBAYARAN QRIS NASIONAL</div>
                                <div style="font-size: 0.78rem; opacity: 0.92;">Quick Response Code Indonesian Standard • GPN</div>
                            </div>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); padding: 5px 14px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
                            NMID: ID1020039482910
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding: 28px 24px; text-align: center;">
                    <div class="text-xs text-muted font-bold uppercase mb-1">Nama Merchant</div>
                    <div style="font-weight: 800; font-size: 1.3rem; color: var(--text-main); margin-bottom: 2px;">
                        PT BOOK STORE INDONESIA
                    </div>
                    <div class="text-xs text-muted mb-4">
                        Cukup scan kode QR di bawah ini menggunakan kamera aplikasi e-wallet atau mobile banking Anda
                    </div>

                    <!-- QR Code Showcase Box -->
                    <div id="qrisContainer" style="display: inline-block; background: #ffffff; padding: 18px; border-radius: 16px; border: 2px dashed #cbd5e1; box-shadow: 0 6px 20px rgba(0,0,0,0.06); margin: 0 auto 20px;">
                        <div id="qrisSvgWrapper" style="display: flex; justify-content: center; align-items: center; min-height: 280px; min-width: 280px; background: #ffffff; border-radius: 8px;">
                            {!! $order->qris_svg !!}
                        </div>
                        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; font-size: 0.82rem; font-weight: 700; color: #475569;">
                            Pesanan: <span style="font-family: monospace; color: var(--primary);">#{{ $order->order_number }}</span> • Nominal: <span style="color: #dc2626;">{{ $order->formatted_grand_total }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons for QRIS -->
                    <div class="d-flex align-center justify-center flex-wrap gap-2 mb-4">
                        <button type="button" class="btn btn-outline btn-sm" onclick="downloadQrisImage()">
                            📥 Unduh Kode QR (PNG)
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="openQrModal()">
                            🔍 Perbesar Kode QR
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="copyToClipboard('{{ $order->qris_payload }}', this)">
                            📋 Salin String Kode QRIS
                        </button>
                    </div>

                    <!-- Supported E-Wallets & Mobile Banking -->
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 16px; text-align: left;" class="mb-4">
                        <div class="text-xs text-muted font-bold uppercase mb-2 text-center">Mendukung Semua Pembayaran Dompet Digital &amp; Mobile Banking:</div>
                        <div class="d-flex align-center justify-center flex-wrap gap-2 text-xs font-bold">
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px;">GoPay</span>
                            <span class="badge" style="background: #f3e8ff; color: #7e22ce; padding: 6px 12px; border-radius: 6px;">OVO</span>
                            <span class="badge" style="background: #dbeafe; color: #1d4ed8; padding: 6px 12px; border-radius: 6px;">DANA</span>
                            <span class="badge" style="background: #ffedd5; color: #c2410c; padding: 6px 12px; border-radius: 6px;">ShopeePay</span>
                            <span class="badge" style="background: #fee2e2; color: #b91c1c; padding: 6px 12px; border-radius: 6px;">LinkAja</span>
                            <span class="badge" style="background: #eff6ff; color: #1e40af; padding: 6px 12px; border-radius: 6px;">BCA Mobile</span>
                            <span class="badge" style="background: #fef9c3; color: #854d0e; padding: 6px 12px; border-radius: 6px;">Livin' Mandiri</span>
                            <span class="badge" style="background: #ecfdf5; color: #047857; padding: 6px 12px; border-radius: 6px;">BRImo</span>
                            <span class="badge" style="background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 6px;">Semua Bank/E-Wallet QRIS</span>
                        </div>
                    </div>

                    <!-- Cara Pembayaran QRIS Steps -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; text-align: left;">
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-bottom: 10px;">
                            📱 Cara Membayar Menggunakan QRIS:
                        </div>
                        <ol style="margin: 0; padding-left: 20px; font-size: 0.88rem; line-height: 1.8; color: var(--text-muted);">
                            <li>Buka aplikasi <strong>e-wallet</strong> (GoPay, OVO, DANA, ShopeePay) atau <strong>mobile banking</strong> (BCA, Mandiri, BRI, BNI, dll) di smartphone Anda.</li>
                            <li>Pilih menu <strong>Scan / Bayar / QRIS</strong> pada aplikasi tersebut.</li>
                            <li>Arahkan kamera ke <strong>Kode QRIS</strong> di atas, atau klik <em>"Unduh Kode QR"</em> dan unggah gambar tersebut dari galeri smartphone Anda.</li>
                            <li>Pastikan nama merchant tertera <strong>PT BOOK STORE INDONESIA</strong> dan nominal tagihan tepat <strong>{{ $order->formatted_grand_total }}</strong>.</li>
                            <li>Masukkan PIN transaksi Anda untuk menyelesaikan pembayaran.</li>
                            <li>Ambil tangkapan layar (screenshot) bukti pembayaran yang sukses, lalu unggah pada form di bawah.</li>
                        </ol>
                    </div>
                </div>
            </div>
        @else
            <!-- BANK TRANSFER ACCOUNTS LIST (KHUSUS METODE TRANSFER BANK) -->
            <div class="card mb-4" style="border-radius: var(--radius-md);">
                <div class="card-header">
                    <h3 class="card-title">🏦 Rekening Tujuan Transfer</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted text-sm mb-3">Silakan lakukan transfer ke salah satu rekening bank resmi BookStore di bawah ini:</p>

                    <div class="d-flex flex-column gap-3">
                        @foreach($bankAccounts as $b)
                            @php
                                $isMatch = isset($selectedBank) && $selectedBank === $b['key'];
                            @endphp
                            <div class="bank-card" style="{{ $isMatch ? 'border: 2px solid var(--primary); background: #f0f7ff;' : '' }}">
                                <div>
                                    <div class="d-flex align-center gap-2">
                                        <span class="text-xs text-muted font-bold">{{ $b['bank'] }} (Kode: {{ $b['code'] }})</span>
                                        @if($isMatch)
                                            <span class="badge badge-primary" style="font-size: 0.7rem; padding: 2px 8px;">PILIHAN ANDA</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 1.3rem; font-weight: 800; font-family: monospace; color: var(--text-main); margin: 3px 0;" id="acc-{{ $b['code'] }}">
                                        {{ $b['number'] }}
                                    </div>
                                    <div class="text-xs text-muted">a.n. <strong>{{ $b['name'] }}</strong></div>
                                </div>
                                <div>
                                    <button type="button" class="btn {{ $isMatch ? 'btn-primary' : 'btn-outline' }} btn-sm copy-btn" onclick="copyToClipboard('{{ $b['number'] }}', this)">
                                        📋 Salin Nomor Rekening
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Upload Proof Form -->
        <div class="card mb-4" style="border-radius: var(--radius-md);">
            <div class="card-header">
                <h3 class="card-title">📤 Upload Bukti Pembayaran</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('orders.payment.upload', $order->order_number) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label class="form-label required">
                            @if($order->isQris())
                                Pilih Foto / Screenshot Bukti Pembayaran QRIS
                            @else
                                Pilih Foto / Screenshot Bukti Transfer Bank
                            @endif
                        </label>
                        <div class="file-upload-dropzone" onclick="document.getElementById('proofInput').click();">
                            <input type="file" name="payment_proof" id="proofInput" accept="image/*" style="display: none;" onchange="previewFile(this)" required>
                            <div id="dropzoneContent">
                                <div style="font-size: 2.5rem; margin-bottom: 8px;">📷</div>
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-bottom: 4px;">
                                    Klik di sini untuk memilih foto bukti pembayaran
                                </div>
                                <div class="text-muted text-xs">
                                    Format: JPG, PNG, WEBP (Maksimal 4 MB)
                                </div>
                            </div>
                            <div id="previewContainer" style="display: none; margin-top: 10px;">
                                <img id="previewImg" src="#" alt="Preview" style="max-height: 200px; border-radius: var(--radius-sm); border: 1px solid var(--border); margin: 0 auto;">
                                <div class="text-xs text-primary font-bold mt-2">✓ File siap diupload</div>
                            </div>
                        </div>
                        <div class="form-hint">
                            @if($order->isQris())
                                Pastikan bukti transaksi QRIS menampilkan nominal, nama merchant (BookStore), dan tanggal pembayaran dengan jelas.
                            @else
                                Pastikan tanggal transfer, nominal, dan nomor referensi rekening terbaca jelas.
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" style="box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                        Kirim Bukti Pembayaran Sekarang →
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-secondary btn-sm">
                ← Kembali ke Detail Pesanan
            </a>
        </div>
    </div>
</div>

<!-- Modal Zoom QRIS -->
<div id="qrisModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; max-width: 440px; width: 100%; padding: 24px; text-align: center; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        <button type="button" onclick="closeQrModal()" style="position: absolute; top: 12px; right: 12px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; line-height: 1;">✕</button>

        <div style="font-weight: 800; font-size: 1.15rem; color: #dc2626; margin-bottom: 4px;">
            QRIS - PT BOOK STORE INDONESIA
        </div>
        <div class="text-xs text-muted mb-3">NMID: ID1020039482910 • Nominal: {{ $order->formatted_grand_total }}</div>

        <div style="display: flex; justify-content: center; align-items: center; padding: 12px; background: #ffffff; border: 2px dashed #e2e8f0; border-radius: 12px; margin-bottom: 16px;">
            {!! \App\Services\QrCodeService::generateSvg($order->qris_payload, 340, 2) !!}
        </div>

        <div class="d-flex align-center justify-center gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="downloadQrisImage()">
                📥 Unduh Gambar QR
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeQrModal()">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text, btn) {
    const cleanText = text.trim();
    navigator.clipboard.writeText(cleanText).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '✓ Disalin!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline');
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline');
        }, 2000);
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = cleanText;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        const orig = btn.innerHTML;
        btn.innerHTML = '✓ Disalin!';
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.classList.remove('btn-success');
        }, 2000);
    });
}

function previewFile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewContainer').style.display = 'block';
            document.getElementById('dropzoneContent').style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function openQrModal() {
    const modal = document.getElementById('qrisModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeQrModal() {
    const modal = document.getElementById('qrisModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQrModal();
    }
});

function downloadQrisImage() {
    const svgEl = document.querySelector('#qrisSvgWrapper svg') || document.querySelector('#qrisModal svg');
    if (!svgEl) return;

    const svgXml = new XMLSerializer().serializeToString(svgEl);
    const svgBlob = new Blob([svgXml], { type: 'image/svg+xml;charset=utf-8' });
    const blobUrl = URL.createObjectURL(svgBlob);
    const img = new Image();

    img.onload = function() {
        const canvas = document.createElement('canvas');
        canvas.width = 640;
        canvas.height = 760;
        const ctx = canvas.getContext('2d');

        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, 640, 760);

        // Red top banner
        ctx.fillStyle = '#dc2626';
        ctx.fillRect(0, 0, 640, 80);

        // QRIS Text
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 30px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('QRIS - STANDAR PEMBAYARAN', 320, 52);

        // Merchant text
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 22px sans-serif';
        ctx.fillText('PT BOOK STORE INDONESIA', 320, 125);

        ctx.fillStyle = '#64748b';
        ctx.font = '14px sans-serif';
        ctx.fillText('NMID: ID1020039482910 • Satu QRIS untuk Semua Pembayaran', 320, 150);

        // Draw QR Image
        ctx.drawImage(img, 120, 175, 400, 400);

        // Order Info Footer
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 18px sans-serif';
        ctx.fillText('Pesanan #{{ $order->order_number }}', 320, 615);

        ctx.fillStyle = '#dc2626';
        ctx.font = 'bold 26px sans-serif';
        ctx.fillText('{{ $order->formatted_grand_total }}', 320, 655);

        ctx.fillStyle = '#64748b';
        ctx.font = '13px sans-serif';
        ctx.fillText('Mendukung GoPay, OVO, DANA, ShopeePay, BCA, Livin, BRImo, dll', 320, 695);

        URL.revokeObjectURL(blobUrl);

        const a = document.createElement('a');
        a.download = 'QRIS-BookStore-{{ $order->order_number }}.png';
        a.href = canvas.toDataURL('image/png');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    img.src = blobUrl;
}
</script>
@endpush
