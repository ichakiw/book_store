@extends('layouts.app')

@section('title', 'BookStore — Toko Buku Pilihan & 100% Original')

@section('content')
<div class="home">
    <!-- 1. Hero Section -->
    <section class="home-hero">
        <div class="container">
            <div class="home-hero-grid">
                <!-- Left Column: Editorial & Search -->
                <div>
                    <div class="home-badge">
                        <span>🌿</span>
                        <span>Kurasi Buku 100% Original &amp; Terpercaya</span>
                    </div>

                    <h1 class="home-title">
                        Buku Pilihan untuk Pikiran yang <span class="home-title-accent">Terus Bertumbuh</span>.
                    </h1>

                    <p class="home-sub">
                        Temukan koleksi bacaan berkualitas dari fiksi berbobot, pengembangan diri, bisnis, hingga sains dan filsafat. Kami mengemas setiap buku dengan rapi agar tiba di tanganmu dengan selamat dan bermakna.
                    </p>

                    <!-- Hero Search Form -->
                    <form action="{{ route('books.index') }}" method="GET" class="home-search-form">
                        <span style="font-size: 1.1rem; color: #94a3b8; margin-right: 8px;">🔍</span>
                        <input type="text" name="search" class="home-search-input" placeholder="Cari judul buku, penulis, atau topik..." autocomplete="off">
                        <button type="submit" class="home-search-btn">
                            <span>Cari Buku</span>
                            <span>→</span>
                        </button>
                    </form>

                    <!-- Quick Discovery Tags -->
                    <div class="home-quick-tags">
                        <span style="font-weight: 700; color: var(--h-ink);">Topik Populer:</span>
                        <a href="{{ route('books.index', ['search' => 'Atomic Habits']) }}" class="home-quick-link">Atomic Habits</a>
                        <a href="{{ route('books.index', ['search' => 'Filosofi Teras']) }}" class="home-quick-link">Filosofi Teras</a>
                        <a href="{{ route('books.index', ['search' => 'Money']) }}" class="home-quick-link">Psychology of Money</a>
                        <a href="{{ route('books.index', ['category' => 'bisnis-finansial']) }}" class="home-quick-link">Bisnis</a>
                    </div>

                    <!-- Trust micro indicators -->
                    <div class="home-hero-trust">
                        <div class="home-hero-trust-item">
                            <span>📦</span>
                            <span>Bubble Wrap &amp; Dus Tebal Gratis</span>
                        </div>
                        <div class="home-hero-trust-item">
                            <span>🛡️</span>
                            <span>Garansi Resmi 100% Asli</span>
                        </div>
                        <div class="home-hero-trust-item">
                            <span>⚡</span>
                            <span>Pengiriman Seluruh Indonesia</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: 3D Visual Book Fan Showcase -->
                <div class="home-showcase">
                    <div class="home-showcase-backdrop"></div>
                    <div class="home-showcase-stack">
                        @if(isset($heroBooks[1]))
                            <a href="{{ route('books.show', $heroBooks[1]->slug) }}" class="home-showcase-book book-back-left" title="{{ $heroBooks[1]->title }}">
                                <img src="{{ $heroBooks[1]->cover_url }}" alt="{{ $heroBooks[1]->title }}" loading="eager">
                            </a>
                        @endif
                        @if(isset($heroBooks[2]))
                            <a href="{{ route('books.show', $heroBooks[2]->slug) }}" class="home-showcase-book book-back-right" title="{{ $heroBooks[2]->title }}">
                                <img src="{{ $heroBooks[2]->cover_url }}" alt="{{ $heroBooks[2]->title }}" loading="eager">
                            </a>
                        @endif
                        @if(isset($heroBooks[0]))
                            <a href="{{ route('books.show', $heroBooks[0]->slug) }}" class="home-showcase-book book-front" title="{{ $heroBooks[0]->title }}">
                                <img src="{{ $heroBooks[0]->cover_url }}" alt="{{ $heroBooks[0]->title }}" loading="eager">
                            </a>
                        @endif
                    </div>

                    <div class="home-showcase-badge">
                        <span>⭐ 4.9 / 5.0</span>
                        <span style="color: var(--h-line);">|</span>
                        <span>1.500+ Pembaca Bahagia</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Value Proposition Pillars -->
    <section class="home-features-section">
        <div class="container">
            <div class="home-features-grid">
                <div class="home-feature-card">
                    <div class="home-feature-icon">🚚</div>
                    <h3 class="home-feature-title">Packing Aman &amp; Ekstra Rapi</h3>
                    <p class="home-feature-desc">Setiap buku dilapisi bubble wrap tebal dan karton proteksi kokoh agar sudut buku tetap sempurna saat tiba.</p>
                </div>
                <div class="home-feature-card">
                    <div class="home-feature-icon">🛡️</div>
                    <h3 class="home-feature-title">100% Buku Asli Bergaransi</h3>
                    <p class="home-feature-desc">Kami hanya menyediakan buku original langsung dari penerbit resmi tanpa kompromi buku bajakan.</p>
                </div>
                <div class="home-feature-card">
                    <div class="home-feature-icon">⚡</div>
                    <h3 class="home-feature-title">Pesanan Cepat &amp; Terlacak</h3>
                    <p class="home-feature-desc">Proses verifikasi pembayaran kilat serta nomor resi pengiriman real-time dari kurir terpercaya.</p>
                </div>
                <div class="home-feature-card">
                    <div class="home-feature-icon">📖</div>
                    <h3 class="home-feature-title">Kurasi Bacaan Terbaik</h3>
                    <p class="home-feature-desc">Pilihan judul buku yang terbukti memberi wawasan baru, memotivasi, dan mendalam untuk dibaca.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Curated Categories Section -->
    @if($categories->count())
    @php
        $categoryStyles = [
            'pengembangan-diri' => ['icon' => '🌱', 'bg' => '#ecfdf5', 'border' => '#a7f3d0'],
            'fiksi-sastra' => ['icon' => '📖', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
            'bisnis-finansial' => ['icon' => '📈', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
            'sains-teknologi' => ['icon' => '🔬', 'bg' => '#f0fdfa', 'border' => '#99f6e4'],
            'sejarah-filsafat' => ['icon' => '🏛️', 'bg' => '#fffbeb', 'border' => '#fde68a'],
            'seni-desain' => ['icon' => '🎨', 'bg' => '#fff1f2', 'border' => '#fecdd3'],
        ];
    @endphp
    <section class="home-section" style="background-color: #fafbfc; border-bottom: 1px solid #f1f5f9;">
        <div class="container">
            <div class="home-section-header">
                <div>
                    <p class="home-eyebrow">Jelajahi Koleksi</p>
                    <h2 class="home-heading">Kategori Buku Pilihan</h2>
                    <p class="home-heading-desc">Pilih kategori yang paling sesuai dengan topik yang ingin kamu pelajari hari ini.</p>
                </div>
                <a href="{{ route('books.index') }}" class="home-view-all">
                    <span>Lihat Semua Kategori</span>
                    <span>→</span>
                </a>
            </div>

            <div class="home-cat-grid">
                @foreach($categories as $category)
                    @php
                        $style = $categoryStyles[$category->slug] ?? ['icon' => '📚', 'bg' => '#f8fafc', 'border' => '#e2e8f0'];
                    @endphp
                    <a href="{{ route('books.index', ['category' => $category->slug]) }}" class="home-cat-box">
                        <div class="home-cat-icon" style="background-color: {{ $style['bg'] }}; border-color: {{ $style['border'] }};">
                            {{ $style['icon'] }}
                        </div>
                        <div class="home-cat-info">
                            <h3 class="home-cat-title">{{ $category->name }}</h3>
                            <p class="home-cat-count">{{ $category->books_count }} judul buku tersedia</p>
                        </div>
                        <div class="home-cat-arrow">→</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 4. Bestseller Showcase Section -->
    @if(isset($bestsellerBooks) && $bestsellerBooks->count())
    <section class="home-section" style="border-bottom: 1px solid #f1f5f9;">
        <div class="container">
            <div class="home-section-header">
                <div>
                    <p class="home-eyebrow">Favorit Pembaca</p>
                    <h2 class="home-heading">Buku Paling Populer &amp; Rekomendasi</h2>
                    <p class="home-heading-desc">Koleksi buku dengan tingkat kepuasan tertinggi dan paling sering dibaca.</p>
                </div>
                <a href="{{ route('books.index', ['sort' => 'termahal']) }}" class="home-view-all">
                    <span>Lihat Koleksi Rekomendasi</span>
                    <span>→</span>
                </a>
            </div>

            <div class="home-bestseller-grid">
                @foreach($bestsellerBooks as $book)
                    <article class="bestseller-card">
                        <a href="{{ route('books.show', $book->slug) }}" class="bestseller-cover-wrap">
                            <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" loading="lazy">
                            <span class="bestseller-pill">
                                <span>🔥</span>
                                <span>Bestseller</span>
                            </span>
                        </a>

                        <div class="bestseller-category">{{ $book->category?->name ?? 'Pilihan' }}</div>
                        <a href="{{ route('books.show', $book->slug) }}" class="bestseller-title">{{ $book->title }}</a>
                        <p class="bestseller-author">oleh {{ $book->author }}</p>

                        <div class="bestseller-foot">
                            <div class="bestseller-price">{{ $book->formatted_price }}</div>
                            <a href="{{ route('books.show', $book->slug) }}" class="bestseller-btn">Lihat Buku</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 5. Latest Arrivals Section -->
    <section class="home-section" style="background-color: #fafbfc; border-bottom: 1px solid #f1f5f9;">
        <div class="container">
            <div class="home-section-header">
                <div>
                    <p class="home-eyebrow">Pembaruan Rak Buku</p>
                    <h2 class="home-heading">Koleksi Buku Terbaru</h2>
                    <p class="home-heading-desc">Judul-judul segar yang baru saja tiba di BookStore dan siap untuk dikirim.</p>
                </div>
                <a href="{{ route('books.index', ['sort' => 'terbaru']) }}" class="home-view-all">
                    <span>Lihat Semua Katalog ({{ $totalBooks ?? 0 }} Buku)</span>
                    <span>→</span>
                </a>
            </div>

            <div class="catalog-grid">
                @foreach($featuredBooks as $book)
                    <article class="catalog-card" style="background: #ffffff; padding: 14px; border: 1px solid var(--h-line); border-radius: 18px; transition: transform .2s, box-shadow .2s;">
                        <a href="{{ route('books.show', $book->slug) }}" class="catalog-card-cover" style="box-shadow: 0 4px 12px -4px rgba(15,23,42,0.12);">
                            <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" loading="lazy">
                            @if($book->stock <= 0)
                                <span class="catalog-card-flag" style="background: rgba(220, 38, 38, 0.88);">Habis</span>
                            @else
                                <span class="catalog-card-flag" style="background: rgba(15, 23, 42, 0.75);">Tersedia</span>
                            @endif
                        </a>
                        <div class="catalog-card-body" style="padding-top: 12px;">
                            <p class="catalog-card-meta">{{ $book->category?->name ?? 'Umum' }}</p>
                            <a href="{{ route('books.show', $book->slug) }}" class="catalog-card-title">{{ $book->title }}</a>
                            <p class="catalog-card-author">{{ $book->author }}</p>
                            <div class="catalog-card-foot" style="padding-top: 10px; border-top: 1px solid #f1f5f9;">
                                <span class="catalog-card-price">{{ $book->formatted_price }}</span>
                                <a href="{{ route('books.show', $book->slug) }}" class="catalog-card-link">Detail</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 6. Editorial & Philosophy Section (About Us) -->
    <section class="home-section" id="tentang">
        <div class="container">
            <div class="home-editorial-card">
                <p class="home-editorial-kicker">Filosofi &amp; Komitmen Kami</p>
                <blockquote class="home-editorial-quote">
                    "Membaca bukan hanya menambah wawasan, tetapi cara paling tenang untuk memahami dunia, menemukan ide baru, dan memperkaya hidup kita."
                </blockquote>

                <div class="home-editorial-grid">
                    <div class="home-editorial-point">
                        <h4><span>🛡️</span> 100% Anti Buku Bajakan</h4>
                        <p>Kami sangat menghormati dedikasi para penulis dan penerbit. Seluruh stok kami dijamin original dengan kertas dan cetakan berkualitas prima.</p>
                    </div>
                    <div class="home-editorial-point">
                        <h4><span>📦</span> Sentuhan Personal Packing</h4>
                        <p>Setiap pesanan diperlakukan istimewa. Kami menggunakan lapisan bubble wrap berlapis dan pelindung kardus tebal agar buku sampai tanpa cacat.</p>
                    </div>
                    <div class="home-editorial-point">
                        <h4><span>🤝</span> Belanja Tanpa Rasa Cemas</h4>
                        <p>Dukungan pelanggan responsif, kemudahan pelacakan resi, serta jaminan penggantian jika terjadi kendala pengiriman.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Call To Action Box -->
    <section class="container">
        <div class="home-cta-box">
            <h2 class="home-cta-title">Siap Memulai Petualangan Membacamu?</h2>
            <p class="home-cta-sub">
                Jelajahi ratusan judul buku pilihan dari penerbit ternama. Nikmati kemudahan memesan buku idaman secara online hari ini.
            </p>
            <div class="d-flex justify-center gap-3 flex-wrap">
                <a href="{{ route('books.index') }}" class="home-cta-btn">
                    <span>Jelajahi Katalog Buku Lengkap</span>
                    <span>→</span>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="btn btn-outline" style="border-radius: 999px; padding: 13px 26px; font-weight: 700;">
                        Daftar Akun Baru
                    </a>
                @endguest
            </div>
        </div>
    </section>
</div>
@endsection
