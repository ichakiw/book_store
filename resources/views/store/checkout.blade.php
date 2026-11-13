@extends('layouts.app')

@section('title', 'Checkout Pesanan - BookStore')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 6px;">
            <a href="{{ route('books.index') }}">Katalog Buku</a> / <a href="{{ route('cart.index') }}">Keranjang</a> / <span>Checkout</span>
        </div>
        <h1 style="font-size: 2rem; margin-bottom: 4px;">Formulir Checkout Pesanan</h1>
        <p class="text-muted text-sm">Lengkapi data penerima dan alamat pengiriman untuk proses pengiriman buku.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <div>
                <strong>Perhatian:</strong> Mohon perbaiki data yang belum lengkap:
                <ul style="margin-left: 20px; margin-top: 6px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <div class="checkout-layout">
            <!-- Left Column: Shipping & Payment Information -->
            <div>
                <!-- Recipient Info Card -->
                <div class="card mb-4" style="border-radius: var(--radius-md);">
                    <div class="card-header">
                        <h3 class="card-title">📍 Data Penerima &amp; Alamat Pengiriman</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label required">Nama Penerima Paket</label>
                                <input type="text" name="recipient_name" value="{{ old('recipient_name', $user->name) }}" class="form-control" pattern="[\p{L}\s]+" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Nomor HP / WhatsApp</label>
                                <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $user->phone) }}" class="form-control" inputmode="numeric" pattern="[0-9]{10,20}" minlength="10" maxlength="20" required>
                                <div class="form-hint">Hanya angka, minimal 10 digit. Kurir akan menghubungi nomor ini saat pengantaran.</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Alamat Lengkap Pengiriman</label>
                            <textarea name="shipping_address" rows="3" class="form-control" placeholder="Contoh: Jl. Merpati Putih No. 12, RT 02 / RW 04, Kel. Sukamaju" required>{{ old('shipping_address', $user->address) }}</textarea>
                        </div>

                        <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label required">Kota / Kabupaten</label>
                                <input type="text" name="city" value="{{ old('city', 'Jakarta Selatan') }}" class="form-control" placeholder="Contoh: Jakarta Selatan" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Kode Pos</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', '12810') }}" class="form-control" placeholder="Contoh: 12810" required>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Catatan Tambahan untuk Kurir (Opsional)</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="form-control" placeholder="Contoh: Titipkan di pos satpam / Rumah pagar hitam">
                        </div>
                    </div>
                </div>

                <!-- Payment Method Card -->
                <div class="card mb-4" style="border-radius: var(--radius-md);">
                    <div class="card-header">
                        <h3 class="card-title">💳 Pilih Metode Pembayaran</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <label class="bank-card" style="cursor: pointer; margin-bottom: 0;">
                                <div class="d-flex align-center gap-3">
                                    <input type="radio" name="payment_method" value="Transfer Bank BCA" checked style="accent-color: var(--primary); width: 20px; height: 20px;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main);">Transfer Bank BCA</div>
                                        <div class="text-xs text-muted">Rekening BCA Virtual / Manual Verification</div>
                                    </div>
                                </div>
                                <div class="bank-logo-badge">BCA</div>
                            </label>

                            <label class="bank-card" style="cursor: pointer; margin-bottom: 0;">
                                <div class="d-flex align-center gap-3">
                                    <input type="radio" name="payment_method" value="Transfer Bank Mandiri" style="accent-color: var(--primary); width: 20px; height: 20px;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main);">Transfer Bank Mandiri</div>
                                        <div class="text-xs text-muted">Rekening Mandiri Corporate</div>
                                    </div>
                                </div>
                                <div class="bank-logo-badge" style="color: #0284c7; background-color: #f0f9ff;">MANDIRI</div>
                            </label>

                            <label class="bank-card" style="cursor: pointer; margin-bottom: 0;">
                                <div class="d-flex align-center gap-3">
                                    <input type="radio" name="payment_method" value="Transfer Bank BRI" style="accent-color: var(--primary); width: 20px; height: 20px;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main);">Transfer Bank BRI</div>
                                        <div class="text-xs text-muted">Rekening BritAma / ATM BRI</div>
                                    </div>
                                </div>
                                <div class="bank-logo-badge" style="color: #059669; background-color: #ecfdf5;">BRI</div>
                            </label>

                            <label class="bank-card" style="cursor: pointer; margin-bottom: 0;">
                                <div class="d-flex align-center gap-3">
                                    <input type="radio" name="payment_method" value="QRIS" style="accent-color: var(--primary); width: 20px; height: 20px;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main);">QRIS (Semua E-Wallet &amp; Mobile Banking)</div>
                                        <div class="text-xs text-muted">Gopay, OVO, Dana, ShopeePay, LinkAja</div>
                                    </div>
                                </div>
                                <div class="bank-logo-badge" style="color: #d97706; background-color: #fffbeb;">QRIS</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div>
                <div class="summary-card">
                    <h3 style="font-size: 1.25rem; margin-bottom: 16px;">Ringkasan Pesanan</h3>

                    <div style="max-height: 240px; overflow-y: auto; margin-bottom: 16px; padding-right: 6px;">
                        @foreach($cartItems as $item)
                            <div class="d-flex align-center justify-between gap-2 mb-3" style="font-size: 0.9rem;">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $item->book->title }}
                                    </div>
                                    <div class="text-xs text-muted">
                                        {{ $item->quantity }} x {{ $item->book->formatted_price }}
                                    </div>
                                </div>
                                <div style="font-weight: 700; color: var(--text-main);">
                                    {{ $item->formatted_subtotal }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="summary-row">
                        <span>Total Buku</span>
                        <strong style="color: var(--text-main);">{{ $cartItems->sum('quantity') }} eks</strong>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal Item</span>
                        <strong style="color: var(--text-main);">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Biaya Pengiriman</span>
                        <strong style="color: var(--text-main);">Rp {{ number_format($shippingCost, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-total">
                        <span>Total Tagihan</span>
                        <span style="color: var(--primary);">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" style="box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                        Buat Pesanan Sekarang →
                    </button>

                    <div class="text-center mt-3 text-xs text-muted">
                        🔒 Transaksi aman dan data Anda terlindungi.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
