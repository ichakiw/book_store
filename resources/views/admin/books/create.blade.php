@extends('layouts.admin')

@section('title', 'Tambah Buku Baru - BookStore Admin')
@section('page_title', 'Tambah Buku Baru')

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
            <h3 class="card-title">📖 Formulir Penambahan Buku</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label required">Judul Buku</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Contoh: Filosofi Teras" required autofocus>
                </div>

                <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label required">Kategori Buku</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Penulis / Pengarang</label>
                        <input type="text" name="author" value="{{ old('author') }}" class="form-control" placeholder="Contoh: Henry Manampiring" required>
                    </div>
                </div>

                <div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Penerbit</label>
                        <input type="text" name="publisher" value="{{ old('publisher') }}" class="form-control" placeholder="Contoh: Kompas Gramedia">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn') }}" class="form-control" placeholder="Contoh: 978-602-412-518-9">
                    </div>
                </div>

                <div class="d-grid" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label required">Harga (Rp)</label>
                        <input type="number" name="price" value="{{ old('price') }}" min="0" step="1000" class="form-control" placeholder="98000" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Jumlah Stok</label>
                        <input type="number" name="stock" value="{{ old('stock', 10) }}" min="0" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jumlah Halaman</label>
                        <input type="number" name="pages" value="{{ old('pages') }}" min="1" class="form-control" placeholder="346">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="published_year" value="{{ old('published_year', date('Y')) }}" min="1900" max="{{ date('Y') + 1 }}" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Foto Cover Buku</label>
                    <input type="file" name="cover_image" accept="image/*" class="form-control" onchange="previewCover(this)">
                    <div class="form-hint">Format didukung: JPG, PNG, WEBP, SVG (Maks. 2 MB). Jika dikosongkan akan menggunakan cover standar.</div>
                    
                    <div id="coverPreviewWrap" style="display: none; margin-top: 12px;">
                        <img id="coverPreview" src="#" alt="Preview Cover" style="width: 100px; height: 140px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Sinopsis / Deskripsi Buku</label>
                    <textarea name="description" rows="5" class="form-control" placeholder="Tuliskan ulasan ringkas mengenai isi buku...">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span class="font-semibold">Aktifkan dan tampilkan buku langsung di etalase toko</span>
                    </label>
                </div>

                <div class="d-flex align-center gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Simpan Buku Baru
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
