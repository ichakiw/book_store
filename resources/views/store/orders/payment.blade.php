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
            <p class="text-muted text-sm">Pesanan <strong>#{{ $order->order_number }}</strong></p>
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
                    <div class="text-xs mt-1">Silakan upload ulang bukti transfer yang benar di bawah ini.</div>
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
                    <span>⚠️</span> Harap transfer tepat sesuai jumlah di atas agar pembayaran dapat terverifikasi otomatis.
                </div>
            </div>
        </div>

        <!-- Bank Accounts List -->
        <div class="card mb-4" style="border-radius: var(--radius-md);">
            <div class="card-header">
                <h3 class="card-title">🏦 Rekening Tujuan Transfer</h3>
            </div>
            <div class="card-body">
                <p class="text-muted text-sm mb-3">Silakan lakukan transfer ke salah satu rekening bank resmi BookStore di bawah ini:</p>

                <div class="d-flex flex-column gap-3">
                    @foreach($bankAccounts as $b)
                        <div class="bank-card">
                            <div>
                                <div class="text-xs text-muted font-bold">{{ $b['bank'] }} (Kode: {{ $b['code'] }})</div>
                                <div style="font-size: 1.3rem; font-weight: 800; font-family: monospace; color: var(--text-main); margin: 3px 0;" id="acc-{{ $b['code'] }}">
                                    {{ $b['number'] }}
                                </div>
                                <div class="text-xs text-muted">a.n. <strong>{{ $b['name'] }}</strong></div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline btn-sm copy-btn" onclick="copyToClipboard('{{ $b['number'] }}', this)">
                                    📋 Salin Nomor
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Upload Proof Form -->
        <div class="card mb-4" style="border-radius: var(--radius-md);">
            <div class="card-header">
                <h3 class="card-title">📤 Upload Bukti Pembayaran</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('orders.payment.upload', $order->order_number) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label class="form-label required">Pilih Foto / Screenshot Bukti Transfer</label>
                        <div class="file-upload-dropzone" onclick="document.getElementById('proofInput').click();">
                            <input type="file" name="payment_proof" id="proofInput" accept="image/*" style="display: none;" onchange="previewFile(this)" required>
                            <div id="dropzoneContent">
                                <div style="font-size: 2.5rem; margin-bottom: 8px;">📷</div>
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-bottom: 4px;">
                                    Klik di sini untuk memilih foto bukti transfer
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
                        <div class="form-hint">Pastikan tanggal transfer, nominal, dan nomor referensi terbaca jelas.</div>
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
@endsection

@push('scripts')
<script>
function copyToClipboard(text, btn) {
    const cleanText = text.replace(/\s+/g, '');
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
</script>
@endpush
