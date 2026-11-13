@extends('layouts.app')

@section('title', '401 Unauthorized - BookStore')

@section('content')
<div class="container" style="padding: 72px 20px; text-align: center; max-width: 620px;">
    <div style="width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; background: #fef2f2; border: 1.5px solid #fecaca; display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
        🚫
    </div>

    <div style="display: inline-block; padding: 4px 14px; background: #fee2e2; color: #dc2626; border-radius: 999px; font-weight: 800; font-size: 0.82rem; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 14px;">
        Error 401 • Unauthorized
    </div>

    <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.03em;">
        Akses Tidak Diizinkan (Unauthorized)
    </h1>

    <p style="font-size: 1rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 32px;">
        {{ $exception->getMessage() ?: 'Anda belum terautentikasi atau belum login ke sistem BookStore. Silakan login terlebih dahulu untuk mengakses halaman ini.' }}
    </p>

    <div class="d-flex align-center justify-center gap-3 flex-wrap">
        <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 11px 26px; border-radius: 999px; font-weight: 700;">
            🔑 Masuk (Login) Sekarang
        </a>
        <a href="{{ route('home') }}" class="btn btn-secondary" style="padding: 11px 22px; border-radius: 999px;">
            ← Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
