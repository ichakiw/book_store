@extends('layouts.app')

@section('title', '403 Forbidden - BookStore')

@section('content')
<div class="container" style="padding: 72px 20px; text-align: center; max-width: 620px;">
    <div style="width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; background: #fffbeb; border: 1.5px solid #fde68a; display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
        🔒
    </div>

    <div style="display: inline-block; padding: 4px 14px; background: #fef3c7; color: #b45309; border-radius: 999px; font-weight: 800; font-size: 0.82rem; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 14px;">
        Error 403 • Forbidden / Access Denied
    </div>

    <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.03em;">
        Akses Ditolak (403 Forbidden)
    </h1>

    <p style="font-size: 1rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 32px;">
        {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses yang sesuai untuk membuka halaman ini (403 Forbidden).' }}
    </p>

    <div class="d-flex align-center justify-center gap-3 flex-wrap">
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="padding: 11px 26px; border-radius: 999px; font-weight: 700;">
                ⚙️ Kembali ke Panel Admin
            </a>
        @else
            <a href="{{ route('home') }}" class="btn btn-primary" style="padding: 11px 26px; border-radius: 999px; font-weight: 700;">
                🏠 Kembali ke Beranda Toko
            </a>
        @endif
        <a href="{{ route('books.index') }}" class="btn btn-secondary" style="padding: 11px 22px; border-radius: 999px;">
            📚 Jelajahi Katalog Buku
        </a>
    </div>
</div>
@endsection
