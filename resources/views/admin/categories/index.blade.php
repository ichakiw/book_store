@extends('layouts.admin')

@section('title', 'Kelola Kategori Buku - BookStore Admin')
@section('page_title', 'Kelola Kategori Buku')

@section('content')
<div class="d-grid" style="grid-template-columns: 2fr 1.2fr; gap: 24px; align-items: flex-start;">
    <!-- Categories List Card -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header">
            <h3 class="card-title">🏷️ Daftar Kategori Buku</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Slug URL</th>
                            <th>Deskripsi</th>
                            <th style="text-align: center;">Jumlah Buku</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td style="font-weight: 700; color: var(--text-main);">
                                    {{ $cat->name }}
                                </td>
                                <td>
                                    <code class="text-xs">{{ $cat->slug }}</code>
                                </td>
                                <td class="text-xs text-muted" style="max-width: 200px;">
                                    {{ Str::limit($cat->description ?? '-', 50) }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-primary">
                                        {{ $cat->books_count }} buku
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btns">
                                        <button type="button" class="action-btn" title="Edit Kategori" 
                                                onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}')">
                                            ✏️
                                        </button>
                                        <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Buku di kategori ini tidak akan terhapus.');" 
                                              style="display: inline; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete" title="Hapus Kategori">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada kategori yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-xs text-muted">
            💡 <em>Jika kategori dihapus oleh admin, riwayat pesanan pelanggan terdahulu tetap terjaga secara utuh.</em>
        </div>
    </div>

    <!-- Create Category Form Card -->
    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header">
            <h3 class="card-title">+ Tambah Kategori Baru</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label required">Nama Kategori</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Komik &amp; Manga" required>
                    @error('name')
                        <div class="text-xs text-danger mt-1">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Kategori (Opsional)</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Penjelasan singkat tentang buku dalam kategori ini...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-xs text-danger mt-1">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Simpan Kategori Baru
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal Dialog -->
<div class="modal-overlay" id="editModalOverlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">✏️ Edit Kategori</h3>
            <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editCategoryForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Kategori</label>
                    <input type="text" name="name" id="editCatName" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="editCatDesc" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(id, name, desc) {
    document.getElementById('editCategoryForm').action = '/admin/kategori/' + id;
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatDesc').value = desc;
    document.getElementById('editModalOverlay').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModalOverlay').classList.remove('active');
}
</script>
@endpush
