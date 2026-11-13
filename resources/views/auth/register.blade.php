@extends('layouts.app')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="container py-5">
    <div style="max-width: 520px; margin: 0 auto;">
        <div class="card" style="box-shadow: var(--shadow-lg); border-radius: var(--radius-lg);">
            <div class="card-body" style="padding: 36px 30px;">
                <div class="text-center mb-4">
                    <div class="brand-icon" style="margin: 0 auto 14px auto; width: 48px; height: 48px; font-size: 1.5rem;">✨</div>
                    <h2 style="font-size: 1.5rem; margin-bottom: 6px;">Buat Akun Pembeli Baru</h2>
                    <p class="text-muted text-sm">Daftar sekarang untuk menikmati kemudahan berbelanja buku berkualitas.</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" style="padding: 10px 14px; font-size: 0.85rem; margin-bottom: 20px;">
                        <div>
                            @foreach($errors->all() as $error)
                                <div>• {{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('register.post') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Contoh: Budi Santoso" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Nomor WhatsApp / HP</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="081234567890" inputmode="numeric" pattern="[0-9]{10,20}" minlength="10" maxlength="20" required>
                        <div class="form-hint">Hanya angka, minimal 10 digit. Digunakan untuk konfirmasi pengiriman pesanan.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Alamat Pengiriman Default</label>
                        <textarea name="address" rows="2" class="form-control" placeholder="Nama Jalan, Nomor Rumah, RT/RW, Kecamatan, Kota" required>{{ old('address') }}</textarea>
                    </div>

                    <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label required">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Ulangi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                        Daftar Sebagai Pembeli
                    </button>
                </form>

                <div class="text-center mt-4 text-sm text-muted">
                    Sudah memiliki akun? <a href="{{ route('login') }}" style="font-weight: 700;">Masuk di Sini</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
