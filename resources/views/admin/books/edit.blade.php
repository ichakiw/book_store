@extends('layouts.admin')

@section('title', 'Edit Buku ' . $book->title . ' - BookStore Admin')
@section('page_title', 'Edit Data Buku')

@section('content')
<div style="max-width: 900px;">
    <div class="mb-3">
        <a href="{{ route('admin.books.index') }}" class="text-sm font-semibold text-muted">
            ← Kembali ke Daftar Buku
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <div>
                <strong>Mohon periksa kesalahan pengisian berikut:</strong>
                <ul style="margin-left: 20px; margin-top: 6px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card" style="border-radius: var(--radius-md);">
        <div class="card-header">
            <h3 class="card-title">✏️ Edit Buku: {{ $book->title }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label required">Judul Buku</label>
                    <input type="text" name="title" value="{{ old('title', $book->title) }}" class="form-control" required autofocus>
                </div>

                <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label required">Kategori Buku</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $book->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Penulis / Pengarang</label>
                        <input type="text" name="author" value="{{ old('author', $book->author) }}" class="form-control" required>
                    </div>
                </div>

                <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Penerbit</label>
                        <input type="text" name="publisher" value="{{ old('publisher', $book->publisher) }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="form-control">
                    </div>
                </div>

                <div class="d-grid" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label required">Harga (Rp)</label>
                        <input type="number" name="price" value="{{ old('price', $book->price) }}" min="0" step="1000" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Jumlah Stok</label>
                        <input type="number" name="stock" value="{{ old('stock', $book->stock) }}" min="0" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jumlah Halaman</label>
                        <input type="number" name="pages" value="{{ old('pages', $book->pages) }}" min="1" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="published_year" value="{{ old('published_year', $book->published_year) }}" min="1900" max="{{ date('Y') + 1 }}" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto Cover Buku</label>
                    <div class="d-flex align-center gap-3 mb-2">
                        <img src="{{ $book->cover_url }}" alt="Cover Saat Ini" style="width: 70px; height: 95px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                        <div>
                            <div class="text-xs text-muted mb-1">Ganti cover buku (opsional):</div>
                            <input type="file" name="cover_image" accept="image/*" class="form-control" onchange="previewCover(this)">
                        </div>
                    </div>
                    <div id="coverPreviewWrap" style="display: none; margin-top: 10px;">
                        <span class="text-xs text-muted">Preview cover baru:</span>
                        <img id="coverPreview" src="#" alt="Preview Cover Baru" style="width: 70px; height: 95px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); display: block; margin-top: 4px;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Sinopsis / Deskripsi Buku</label>
                    <textarea name="description" rows="5" class="form-control">{{ old('description', $book->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $book->is_active) ? 'checked' : '' }}>
                        <span class="font-semibold">Buku ini aktif dan tampil di etalase toko</span>
                    </label>
                </div>

                <div class="d-flex align-center gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Simpan Perubahan Data
                    </button>
                    <a href="{{ route('admin.books.index') }}" class="btn btn-secondary btn-lg">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('coverPreview').src = e.target.result;
            document.getElementById('coverPreviewWrap').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
