@extends('layouts.admin')

@section('title', 'Detail Pelanggan ' . $customer->name . ' - BookStore Admin')
@section('page_title', 'Detail Profil Pelanggan')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.customers.index') }}" class="text-sm font-semibold text-muted">
        ← Kembali ke Daftar Pelanggan
    </a>
</div>

<div class="d-grid" style="grid-template-columns: 1fr 2fr; gap: 24px; align-items: flex-start;">
    <!-- Profile Card -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header">
            <h3 class="card-title">👤 Informasi Profil</h3>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.6rem; margin: 0 auto 12px auto;">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <h3 style="margin-bottom: 2px;">{{ $customer->name }}</h3>
                <div class="text-sm text-muted">{{ $customer->email }}</div>
                <div style="margin-top: 8px;">
                    <span class="badge {{ $customer->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                        {{ $customer->status === 'active' ? 'Akun Aktif' : 'Akun Diblokir' }}
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <div class="text-xs text-muted font-bold uppercase">Nomor HP / WA</div>
                <div style="font-weight: 600;">{{ $customer->phone ?? '-' }}</div>
            </div>

            <div class="mb-3">
                <div class="text-xs text-muted font-bold uppercase">Alamat Pengiriman</div>
                <div style="font-size: 0.92rem; line-height: 1.4;">{{ $customer->address ?? '-' }}</div>
            </div>

            <div class="mb-3">
                <div class="text-xs text-muted font-bold uppercase">Total Belanja (Selesai)</div>
                <div style="font-size: 1.3rem; font-weight: 800; color: var(--success);">
                    Rp {{ number_format($totalSpent, 0, ',', '.') }}
                </div>
            </div>

            <div class="pt-3" style="border-top: 1px solid var(--border-light);">
                <form action="{{ route('admin.customers.toggle_status', $customer->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-block {{ $customer->status === 'active' ? 'btn-outline-danger' : 'btn-success' }} btn-sm">
                        {{ $customer->status === 'active' ? '🔒 Blokir Akun Pelanggan' : '🔓 Aktifkan Kembali Akun' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Orders History Card -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header">
            <h3 class="card-title">📦 Riwayat Pesanan Pelanggan ({{ $customer->orders->count() }})</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Tanggal</th>
                            <th style="text-align: right;">Total Tagihan</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->orders as $o)
                            <tr>
                                <td style="font-weight: 700;">
                                    <a href="{{ route('admin.orders.show', $o->id) }}">
                                        {{ $o->order_number }}
                                    </a>
                                </td>
                                <td class="text-xs text-muted">
                                    {{ $o->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--primary);">
                                    {{ $o->formatted_grand_total }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge {{ $o->status_badge_class }}">
                                        {{ $o->status_label }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="btn btn-outline btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    Pelanggan ini belum melakukan transaksi pesanan apa pun.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
