<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - BookStore Backoffice</title>
    
    <!-- Custom CSS (Pure CSS - No Tailwind) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <span style="font-size: 1.5rem;">📚</span>
                <span>BookStore <small style="font-size: 0.65rem; background: rgba(37,99,235,0.4); padding: 2px 6px; border-radius: 4px; vertical-align: middle;">ADMIN</small></span>
            </a>

            @php
                $pendingOrdersCount = \App\Models\Order::where('status', 'menunggu_konfirmasi')->count();
                $lowStockCount = \App\Models\Book::where('stock', '<=', 5)->count();
            @endphp

            <div class="sidebar-section-title">Menu Utama</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span>📊</span> Dashboard
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-title">Katalog &amp; Inventori</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('admin.books.index') }}" class="sidebar-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}">
                        <span>📖</span> Kelola Buku
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <span>🏷️</span> Kelola Kategori
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.stocks.index') }}" class="sidebar-link {{ request()->routeIs('admin.stocks.*') ? 'active' : '' }}">
                        <span>📦</span> Kelola Stok Buku
                        @if($lowStockCount > 0)
                            <span class="sidebar-badge" style="background-color: var(--danger); color: #ffffff;">{{ $lowStockCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-title">Transaksi &amp; Pembeli</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <span>🛒</span> Kelola Pesanan
                        @if($pendingOrdersCount > 0)
                            <span class="sidebar-badge" style="background-color: var(--warning); color: #ffffff;">{{ $pendingOrdersCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.customers.index') }}" class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <span>👥</span> Kelola Pelanggan
                    </a>
                </li>
                <li>
                    @php
                        $unreadChatCount = \App\Models\ChatMessage::where('is_read', false)
                            ->whereHas('sender', fn($q) => $q->where('role', '!=', 'admin'))
                            ->count();
                    @endphp
                    <a href="{{ route('admin.chat.index') }}" class="sidebar-link {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                        <span>💬</span> Chat Pelanggan
                        @if($unreadChatCount > 0)
                            <span class="sidebar-badge" style="background-color: var(--primary); color: #ffffff;">{{ $unreadChatCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-title">Analisis &amp; Laporan</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <span>📈</span> Laporan Penjualan
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="{{ route('books.index') }}" class="btn btn-sm btn-secondary" style="width: 100%; justify-content: center;" target="_blank">
                    🌐 Buka Katalog Toko
                </a>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="d-flex align-center gap-3">
                    <button type="button" class="btn btn-sm btn-secondary d-none" id="sidebarToggle" style="padding: 6px 10px;">
                        ☰
                    </button>
                    <h2 class="topbar-title">@yield('page_title', 'Dashboard')</h2>
                </div>

                <div class="topbar-actions">
                    <div class="d-flex align-center gap-2" style="font-size: 0.9rem; font-weight: 600; color: var(--text-main);">
                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem; background: var(--secondary);">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span>{{ auth()->user()->name }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
                            🚪 Keluar
                        </button>
                    </form>
                </div>
            </header>

            <!-- Alerts -->
            <div style="padding: 24px 28px 0 28px;">
                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <span style="font-size: 1.2rem;">⚠️</span>
                        <div style="flex: 1;">
                            <strong style="font-weight: 700;">Gagal Menyimpan Data:</strong>
                            <ul style="margin: 6px 0 0 18px; padding: 0; line-height: 1.5;">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success flash-alert" data-timeout="3000">
                        <span style="font-size: 1.2rem;">✅</span>
                        <div style="flex: 1;">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger flash-alert">
                        <span style="font-size: 1.2rem;">⚠️</span>
                        <div style="flex: 1;">{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning flash-alert">
                        <span style="font-size: 1.2rem;">🔔</span>
                        <div style="flex: 1;">{{ session('warning') }}</div>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    <script>
        document.querySelectorAll('.flash-alert[data-timeout]').forEach(function(el) {
            var timeout = parseInt(el.dataset.timeout, 10) || 3000;
            setTimeout(function() {
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 300);
            }, timeout);
        });
    </script>
</body>
</html>
