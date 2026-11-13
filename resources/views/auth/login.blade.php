@extends('layouts.app')

@section('title', 'Masuk ke Akun Anda')

@section('content')
<div class="container py-5">
    <div style="max-width: 440px; margin: 0 auto;">
        <div class="card" style="box-shadow: var(--shadow-lg); border-radius: var(--radius-lg);">
            <div class="card-body" style="padding: 36px 30px;">
                <div class="text-center mb-4">
                    <div class="brand-icon" style="margin: 0 auto 14px auto; width: 48px; height: 48px; font-size: 1.5rem;">📚</div>
                    <h2 style="font-size: 1.5rem; margin-bottom: 6px;">Selamat Datang Kembali</h2>
                    <p class="text-muted text-sm">Masuk untuk melanjutkan pembelian dan memantau status pesanan.</p>
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

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label required">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required autofocus>
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label class="form-label required" style="margin: 0;">Password</label>
                        </div>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="remember">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                        Masuk Sekarang
                    </button>
                </form>

                <!-- Quick Test Credentials Box -->
                <div style="margin-top: 24px; padding: 14px; background-color: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed #cbd5e1; font-size: 0.82rem;">
                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 6px;">💡 Akun Demo untuk Testing:</div>
                    <div style="margin-bottom: 4px;">
                        <strong>Admin:</strong> <code>admin@bookstore.com</code> / <code>password</code>
                    </div>
                    <div>
                        <strong>Pembeli:</strong> <code>user@bookstore.com</code> / <code>password</code>
                    </div>
                </div>

                <div class="text-center mt-4 text-sm text-muted">
                    Belum memiliki akun? <a href="{{ route('register') }}" style="font-weight: 700;">Daftar Akun Baru</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
