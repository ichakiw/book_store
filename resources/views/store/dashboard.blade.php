@extends('layouts.app')

@section('title', 'Dashboard Akun Saya - BookStore')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex align-center justify-between flex-wrap gap-3 mb-4">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 4px;">Halo, {{ $user->name }}! 👋</h1>
            <p class="text-muted text-sm">Kelola informasi profil akun Anda dan pantau seluruh transaksi pembelian buku.</p>
        </div>
        <div>
            <a href="{{ route('books.index') }}" class="btn btn-primary btn-sm">
                📚 Belanja Buku Baru
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="d-grid mb-4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-body d-flex align-center gap-3">
                <div class="stat-icon stat-icon-blue">📦</div>
                <div>
                    <div class="text-xs text-muted font-bold uppercase">Total Pesanan</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $stats['total_orders'] }}</div>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-body d-flex align-center gap-3">
                <div class="stat-icon stat-icon-orange">⏳</div>
                <div>
                    <div class="text-xs text-muted font-bold uppercase">Perlu Dibayar</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--warning);">{{ $stats['pending_payment'] }}</div>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-body d-flex align-center gap-3">
                <div class="stat-icon stat-icon-purple">🚚</div>
                <div>
                    <div class="text-xs text-muted font-bold uppercase">Sedang Berjalan</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #9333ea;">{{ $stats['processing'] }}</div>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-body d-flex align-center gap-3">
                <div class="stat-icon stat-icon-green">✓</div>
                <div>
                    <div class="text-xs text-muted font-bold uppercase">Pesanan Selesai</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--success);">{{ $stats['completed'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Column: Left Profile Edit, Right Recent Orders -->
    <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 24px; align-items: flex-start;">
        <!-- Profile Form Card -->
        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-header">
                <h3 class="card-title">👤 Informasi Profil &amp; Alamat Saya</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" pattern="[\p{L}\s]+" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Nomor HP / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control" inputmode="numeric" pattern="[0-9]{10,20}" minlength="10" maxlength="20" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Alamat Lengkap Pengiriman</label>
                        <textarea name="address" rows="3" class="form-control" required>{{ old('address', $user->address) }}</textarea>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">

                    <div class="text-sm font-bold text-muted mb-3">GANTI PASSWORD (OPSIONAL)</div>

                    <div class="form-group">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password">
                    </div>

                    <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" minlength="8">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Simpan Perubahan Profil
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Orders Card -->
        <div class="card" style="border-radius: var(--radius-md);">
            <div class="card-header d-flex justify-between align-center">
                <h3 class="card-title">📦 Pesanan Terakhir Anda</h3>
                <a href="{{ route('orders.index') }}" class="text-xs font-bold text-primary">Lihat Semua →</a>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('orders.show', $order->order_number) }}" style="font-weight: 700;">
                                                {{ $order->order_number }}
                                            </a>
                                            <div class="text-xs text-muted">{{ $order->created_at->format('d M Y') }}</div>
                                        </td>
                                        <td style="font-weight: 700;">
                                            {{ $order->formatted_grand_total }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $order->status_badge_class }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-outline btn-sm">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div style="font-size: 2.5rem; margin-bottom: 8px;">📖</div>
                        <div class="text-muted text-sm">Belum ada riwayat transaksi.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
