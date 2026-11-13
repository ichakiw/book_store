@extends('layouts.admin')

@section('title', 'Kelola Pelanggan - BookStore Admin')
@section('page_title', 'Kelola Data Pelanggan')

@section('content')
<!-- Summary Cards -->
<div class="stat-cards-grid mb-4">
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Pelanggan</span>
            <span class="stat-value">{{ $stats['total'] }}</span>
        </div>
        <div class="stat-icon stat-icon-blue">👥</div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Pelanggan Aktif</span>
            <span class="stat-value" style="color: var(--success);">{{ $stats['active'] }}</span>
        </div>
        <div class="stat-icon stat-icon-green">✓</div>
    </div>

    <div class="stat-card" style="{{ $stats['blocked'] > 0 ? 'border-color: var(--danger-border);' : '' }}">
        <div class="stat-info">
            <span class="stat-label">Akun Diblokir</span>
            <span class="stat-value" style="color: var(--danger);">{{ $stats['blocked'] }}</span>
        </div>
        <div class="stat-icon stat-icon-red">🔒</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex align-center gap-3 flex-wrap flex-1">
        <div style="min-width: 260px; flex: 1;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, nomor HP pelanggan..." class="form-control" style="padding: 8px 14px;">
        </div>

        <div>
            <select name="status" class="form-select" style="padding: 8px 14px; width: auto;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Diblokir</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Customers Table Card -->
<div class="card" style="border-radius: var(--radius-md);">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Email &amp; Kontak</th>
                        <th>Alamat Lengkap</th>
                        <th style="text-align: center;">Total Transaksi</th>
                        <th style="text-align: center;">Status Akun</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main);">{{ $c->name }}</div>
                                <div class="text-xs text-muted">Terdaftar: {{ $c->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div>{{ $c->email }}</div>
                                <div class="text-xs text-muted">📞 {{ $c->phone ?? '-' }}</div>
                            </td>
                            <td class="text-xs" style="max-width: 250px; line-height: 1.4;">
                                {{ $c->address ?? '-' }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-primary">
                                    {{ $c->orders_count }} pesanan
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge {{ $c->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $c->status === 'active' ? 'Aktif' : 'Diblokir' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns">
                                    <a href="{{ route('admin.customers.show', $c->id) }}" class="action-btn" title="Lihat Profil & Riwayat Pesanan">
                                        👁️
                                    </a>

                                    <form action="{{ route('admin.customers.toggle_status', $c->id) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun pelanggan ini?');" 
                                          style="display: inline; margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="action-btn" title="{{ $c->status === 'active' ? 'Blokir Akun' : 'Aktifkan Akun' }}">
                                            {{ $c->status === 'active' ? '🔒' : '🔓' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.customers.destroy', $c->id) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pelanggan ini?');" 
                                          style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Hapus Pelanggan">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Tidak ada data pelanggan yang sesuai dengan kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-center">
    {{ $customers->links() }}
</div>
@endsection
