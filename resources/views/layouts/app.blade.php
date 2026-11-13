<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BookStore') - Toko Buku Online Modern</title>
    
    <!-- Custom CSS (Pure CSS - No Tailwind) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-inner">
            <!-- Brand -->
            <a href="{{ route('home') }}" class="brand-logo">
                <div class="brand-icon">📚</div>
                <span>BookStore</span>
            </a>

            <!-- Navigation Links & Actions -->
            <div class="d-flex align-center gap-3">
                <ul class="nav-links">
                    <li>
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                    </li>
                    <li>
                        <a href="{{ route('books.index') }}" class="nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}">Katalog</a>
                    </li>
                </ul>

                @auth
                    <!-- Cart Button with Counter -->
                    @php
                        $cartCount = \App\Models\Cart::where('user_id', auth()->id())->sum('quantity');
                    @endphp
                    <a href="{{ route('cart.index') }}" class="cart-btn" title="Keranjang Belanja">
                        🛒
                        @if($cartCount > 0)
                            <span class="cart-badge">{{ $cartCount }}</span>
                        @endif
                    </a>

                    <!-- User Dropdown Menu -->
                    <div class="user-menu">
                        <button type="button" class="user-btn">
                            <div class="user-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span>{{ Str::limit(auth()->user()->name, 14) }}</span>
                            <span style="font-size: 0.75rem; color: var(--text-light);">▼</span>
                        </button>
                        <div class="dropdown-panel">
                            <div style="padding: 10px 18px; border-bottom: 1px solid var(--border);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-main);">{{ auth()->user()->name }}</div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ auth()->user()->email }}</div>
                            </div>

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item" style="color: var(--primary); font-weight: 700;">
                                    ⚙️ Panel Administrator
                                </a>
                                <div class="dropdown-divider"></div>
                            @endif

                            <a href="{{ route('user.dashboard') }}" class="dropdown-item">
                                👤 Akun &amp; Profil
                            </a>
                            <a href="{{ route('chat.index') }}" class="dropdown-item">
                                💬 Chat dengan Admin
                            </a>
                            <a href="{{ route('orders.index') }}" class="dropdown-item">
                                📦 Riwayat Pesanan
                            </a>
                            <a href="{{ route('cart.index') }}" class="dropdown-item">
                                🛒 Keranjang Belanja
                            </a>

                            <div class="dropdown-divider"></div>
                            
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; color: var(--danger);">
                                    🚪 Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-center gap-2">
                        <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Flash Alerts -->
    <div class="container mt-3">
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

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="d-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 32px;">
                <div>
                    <div class="d-flex align-center gap-2 mb-2" style="color: #ffffff; font-weight: 800; font-size: 1.3rem;">
                        <span>📚</span> BookStore
                    </div>
                    <p style="font-size: 0.88rem; line-height: 1.6; margin-bottom: 16px;">
                        Toko buku online terlengkap dan terpercaya di Indonesia. Menyediakan ribuan judul buku fiksi, non-fiksi, sains, teknologi, dan pengembangan diri dengan jaminan 100% original.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge badge-primary">100% Original</span>
                        <span class="badge badge-success">Pengiriman Cepat</span>
                    </div>
                </div>

                <div>
                    <h4>Kategori Populer</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('books.index', ['category' => 'pengembangan-diri']) }}">Pengembangan Diri</a></li>
                        <li><a href="{{ route('books.index', ['category' => 'fiksi-sastra']) }}">Fiksi &amp; Sastra</a></li>
                        <li><a href="{{ route('books.index', ['category' => 'bisnis-finansial']) }}">Bisnis &amp; Finansial</a></li>
                        <li><a href="{{ route('books.index', ['category' => 'sains-teknologi']) }}">Sains &amp; Teknologi</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Layanan Pelanggan</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('orders.index') }}">Cek Status Pesanan</a></li>
                        <li><a href="{{ route('cart.index') }}">Keranjang Belanja</a></li>
                        <li><a href="{{ route('user.dashboard') }}">Akun Pengguna</a></li>
                        <li><a href="#">Panduan Pembayaran</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Hubungi Kami</h4>
                    <p style="font-size: 0.88rem; margin-bottom: 8px;">📍 Jakarta Pusat, DKI Jakarta</p>
                    <p style="font-size: 0.88rem; margin-bottom: 8px;">📞 WhatsApp: 0812-3456-7890</p>
                    <p style="font-size: 0.88rem; margin-bottom: 8px;">✉️ support@bookstore.id</p>
                    <p style="font-size: 0.82rem; color: var(--text-light); margin-top: 12px;">Senin - Minggu: 08.00 - 21.00 WIB</p>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; {{ date('Y') }} BookStore Indonesia. All rights reserved.</div>
                <div class="d-flex gap-3">
                    <span style="color: #64748b;">Syarat &amp; Ketentuan</span>
                    <span style="color: #64748b;">Kebijakan Privasi</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Chat Widget -->
    <div id="floatingChatBox" style="position: fixed; bottom: 88px; right: 24px; width: 360px; height: 500px; max-width: calc(100vw - 32px); background: #ffffff; border-radius: 20px; box-shadow: 0 20px 50px -10px rgba(15,23,42,0.28); border: 1px solid var(--border); display: none; flex-direction: column; z-index: 1000; overflow: hidden;">
        <!-- Widget Header -->
        <div style="padding: 14px 18px; background: var(--text-main); color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div class="d-flex align-center gap-2">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                    💬
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 0.92rem; line-height: 1.2;">Admin BookStore</div>
                    <div style="font-size: 0.72rem; color: #94a3b8;">Siap membantu pertanyaan Anda</div>
                </div>
            </div>
            <div class="d-flex align-center gap-2">
                @auth
                    <a href="{{ route('chat.index') }}" title="Buka Halaman Penuh" style="color: #cbd5e1; font-size: 0.82rem; text-decoration: none; padding: 2px 6px;">⤢</a>
                @endauth
                <button type="button" onclick="toggleFloatingChat()" style="background: none; border: none; color: #ffffff; font-size: 1.2rem; cursor: pointer; padding: 0 4px;">&times;</button>
            </div>
        </div>

        @auth
            <!-- Widget Messages Area -->
            <div id="floatingChatMessages" style="flex: 1; overflow-y: auto; padding: 16px; background: #f8fafc; display: flex; flex-direction: column; gap: 10px;">
                <div style="font-size: 0.8rem; background: #ffffff; border: 1px solid var(--border); padding: 10px 14px; border-radius: 12px; border-bottom-left-radius: 2px; color: var(--text-main); line-height: 1.4;">
                    Halo <strong>{{ auth()->user()->name }}</strong>! 👋 Butuh informasi buku atau bantuan pesanan? Tuliskan pesanmu di bawah ya.
                </div>
            </div>

            <!-- Widget Send Form -->
            <form id="floatingChatForm" onsubmit="sendFloatingMessage(event)" style="padding: 10px 14px; border-top: 1px solid var(--border); background: #ffffff; display: flex; gap: 8px; margin: 0; align-items: center;">
                <input type="text" id="floatingMessageInput" placeholder="Ketik pesan..." class="form-control" style="font-size: 0.88rem; padding: 8px 12px; border-radius: 999px;" autocomplete="off" required>
                <button type="submit" id="floatingSendBtn" class="btn btn-primary btn-sm" style="border-radius: 999px; padding: 8px 16px; font-weight: 700; flex-shrink: 0;">
                    Kirim
                </button>
            </form>
        @else
            <!-- Guest Prompt -->
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 24px; text-align: center; background: #f8fafc;">
                <div style="font-size: 2.8rem; margin-bottom: 12px;">💬</div>
                <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-main); margin-bottom: 6px;">Chat dengan Admin</div>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;">
                    Silakan masuk ke akun Anda terlebih dahulu untuk memulai percakapan langsung dengan customer service kami.
                </p>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm" style="padding: 9px 22px; border-radius: 999px; font-weight: 700;">
                    🔑 Masuk (Login) Sekarang
                </a>
            </div>
        @endauth
    </div>

    <!-- Floating Chat Trigger Button -->
    <div onclick="toggleFloatingChat()" class="floating-chat-trigger" style="position: fixed; bottom: 24px; right: 24px; z-index: 999; background: var(--text-main); color: #ffffff; padding: 12px 20px; border-radius: 999px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.28); display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; transition: transform .2s, box-shadow .2s; border: 1.5px solid rgba(255,255,255,0.15);">
        <span style="font-size: 1.15rem;">💬</span>
        <span>Chat Admin</span>
        @auth
            @php
                $hasUnreadAdmin = \App\Models\ChatMessage::where('user_id', auth()->id())
                    ->where('sender_id', '!=', auth()->id())
                    ->where('is_read', false)
                    ->exists();
            @endphp
            @if($hasUnreadAdmin)
                <span style="width: 9px; height: 9px; background: #ef4444; border-radius: 50%; box-shadow: 0 0 0 2px #ffffff;"></span>
            @endif
        @endauth
    </div>

    @stack('scripts')
    <script>
        function toggleFloatingChat() {
            var box = document.getElementById('floatingChatBox');
            if (box.style.display === 'flex') {
                box.style.display = 'none';
            } else {
                box.style.display = 'flex';
                @auth
                    fetchFloatingMessages();
                    setTimeout(function() {
                        var input = document.getElementById('floatingMessageInput');
                        if (input) input.focus();
                    }, 100);
                @endauth
            }
        }

        @auth
        function fetchFloatingMessages() {
            var box = document.getElementById('floatingChatMessages');
            if (!box) return;
            fetch('{{ route("chat.messages") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success && data.messages) {
                    var base = '<div style="font-size: 0.8rem; background: #ffffff; border: 1px solid var(--border); padding: 10px 14px; border-radius: 12px; border-bottom-left-radius: 2px; color: var(--text-main); line-height: 1.4;">' +
                        'Halo <strong>{{ addslashes(auth()->user()->name) }}</strong>! 👋 Butuh informasi buku atau bantuan pesanan? Tuliskan pesanmu di bawah ya.</div>';
                    var html = base;
                    data.messages.forEach(function(msg) {
                        var isMe = msg.is_me;
                        html += '<div style="display: flex; flex-direction: column; align-items: ' + (isMe ? 'flex-end' : 'flex-start') + ';">';
                        html += '<div style="font-size: 0.68rem; color: var(--text-muted); margin-bottom: 2px;">' + (isMe ? 'Anda' : 'Admin') + ' • ' + msg.time + '</div>';
                        html += '<div style="max-width: 80%; padding: 8px 12px; border-radius: 12px; font-size: 0.85rem; line-height: 1.4; ' + (isMe ? 'background: var(--primary); color: #ffffff; border-bottom-right-radius: 2px;' : 'background: #ffffff; color: var(--text-main); border: 1px solid var(--border); border-bottom-left-radius: 2px;') + '">';
                        var div = document.createElement('div');
                        div.innerText = msg.message;
                        html += div.innerHTML.replace(/\n/g, '<br>');
                        html += '</div></div>';
                    });
                    box.innerHTML = html;
                    box.scrollTop = box.scrollHeight;
                }
            })
            .catch(function(e) { console.error(e); });
        }

        function sendFloatingMessage(e) {
            e.preventDefault();
            var input = document.getElementById('floatingMessageInput');
            var btn = document.getElementById('floatingSendBtn');
            var msg = input.value.trim();
            if (!msg) return;

            btn.disabled = true;
            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: msg })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (data.success) {
                    input.value = '';
                    fetchFloatingMessages();
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                alert('Gagal mengirim pesan.');
            });
        }
        @endauth

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.flash-alert').forEach(function (el) {
                setTimeout(function () {
                    el.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-6px)';
                    setTimeout(function () { el.remove(); }, 350);
                }, 3000);
            });
        });
    </script>
</body>
</html>
