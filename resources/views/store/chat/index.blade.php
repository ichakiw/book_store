@extends('layouts.app')

@section('title', 'Chat dengan Admin - BookStore')

@section('content')
<div class="container" style="padding-top: 32px; padding-bottom: 56px;">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
        <a href="{{ route('home') }}" style="color: var(--text-muted);">Beranda</a> &gt; 
        <span style="color: var(--text-main); font-weight: 700;">Chat Admin</span>
    </div>

    <div class="card" style="max-width: 860px; margin: 0 auto; border-radius: var(--radius-md); overflow: hidden; height: 640px; display: flex; flex-direction: column; box-shadow: var(--shadow-md);">
        <!-- Header -->
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div class="d-flex align-center gap-3">
                <div style="position: relative;">
                    <div class="user-avatar" style="width: 46px; height: 46px; border-radius: 50%; background: var(--primary); color: #ffffff; font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                        📚
                    </div>
                    <span style="position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; background: #10b981; border: 2px solid #ffffff; border-radius: 50%;"></span>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                        <span>Customer Service &amp; Admin</span>
                        <span class="badge badge-success" style="font-size: 0.7rem; padding: 2px 8px;">Online</span>
                    </div>
                    <div class="text-xs text-muted">
                        Tanyakan stok buku, konfirmasi pesanan, rekomendasi bacaan, atau bantuan transaksi.
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-secondary">
                    📦 Pesanan Saya
                </a>
            </div>
        </div>

        <!-- Messages Area -->
        <div style="flex: 1; overflow-y: auto; padding: 24px; background: #f8fafc; display: flex; flex-direction: column; gap: 14px;" id="userChatScrollArea">
            <!-- Welcome Bot / Admin Greeting -->
            <div style="display: flex; gap: 10px; align-items: flex-start; max-width: 75%;">
                <div class="user-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-size: 0.85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    📚
                </div>
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 16px; border-bottom-left-radius: 4px; padding: 12px 18px; font-size: 0.92rem; color: var(--text-main); line-height: 1.5; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                    Halo <strong>{{ auth()->user()->name }}</strong>! 👋 Selamat datang di layanan bantuan BookStore. Ada yang bisa kami bantu seputar buku atau pesanan Anda hari ini?
                </div>
            </div>

            @foreach($messages as $msg)
                @php $isMe = $msg->isFromCustomer(); @endphp
                <div style="display: flex; flex-direction: column; align-items: {{ $isMe ? 'flex-end' : 'flex-start' }};">
                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 4px; padding: 0 4px;">
                        {{ $isMe ? 'Anda' : 'Admin BookStore' }} • {{ $msg->created_at->format('H:i') }}
                    </div>
                    <div style="max-width: 72%; padding: 12px 18px; border-radius: 16px; font-size: 0.92rem; line-height: 1.5; {{ $isMe ? 'background: var(--primary); color: #ffffff; border-bottom-right-radius: 4px;' : 'background: #ffffff; color: var(--text-main); border: 1px solid var(--border); border-bottom-left-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);' }}">
                        {!! nl2br(e($msg->message)) !!}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Input Box -->
        <div style="padding: 18px 24px; border-top: 1px solid var(--border); background: #ffffff;">
            <form id="userChatForm" onsubmit="sendUserMessage(event)" style="margin: 0; display: flex; gap: 12px; align-items: flex-end;">
                @csrf
                <div style="flex: 1;">
                    <textarea id="userChatMessageInput" rows="2" class="form-control" placeholder="Ketik pesan untuk Admin BookStore di sini..." style="resize: none; font-size: 0.92rem;" required></textarea>
                </div>
                <button type="submit" id="userChatSendBtn" class="btn btn-primary" style="height: 52px; padding: 0 24px; font-weight: 700; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 8px;">
                    <span>Kirim</span>
                    <span>✉️</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var chatScroll = document.getElementById('userChatScrollArea');

function scrollToBottom() {
    if (chatScroll) {
        chatScroll.scrollTop = chatScroll.scrollHeight;
    }
}
scrollToBottom();

// Poll for replies every 4 seconds
setInterval(function() {
    fetch('{{ route("chat.messages") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success && data.messages) {
            renderCustomerMessages(data.messages);
        }
    })
    .catch(function(err) { console.error('Poll error', err); });
}, 4000);

function escapeHtml(text) {
    var div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

function renderCustomerMessages(messages) {
    if (!chatScroll) return;
    var baseWelcome = '<div style="display: flex; gap: 10px; align-items: flex-start; max-width: 75%;">' +
        '<div class="user-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-size: 0.85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">📚</div>' +
        '<div style="background: #ffffff; border: 1px solid var(--border); border-radius: 16px; border-bottom-left-radius: 4px; padding: 12px 18px; font-size: 0.92rem; color: var(--text-main); line-height: 1.5; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">' +
        'Halo <strong>{{ addslashes(auth()->user()->name) }}</strong>! 👋 Selamat datang di layanan bantuan BookStore. Ada yang bisa kami bantu seputar buku atau pesanan Anda hari ini?</div></div>';

    var html = baseWelcome;
    messages.forEach(function(msg) {
        var isMe = msg.is_me;
        html += '<div style="display: flex; flex-direction: column; align-items: ' + (isMe ? 'flex-end' : 'flex-start') + ';">';
        html += '<div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 4px; padding: 0 4px;">' + (isMe ? 'Anda' : 'Admin BookStore') + ' • ' + msg.time + '</div>';
        html += '<div style="max-width: 72%; padding: 12px 18px; border-radius: 16px; font-size: 0.92rem; line-height: 1.5; ' + (isMe ? 'background: var(--primary); color: #ffffff; border-bottom-right-radius: 4px;' : 'background: #ffffff; color: var(--text-main); border: 1px solid var(--border); border-bottom-left-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);') + '">';
        html += escapeHtml(msg.message).replace(/\n/g, '<br>');
        html += '</div></div>';
    });
    chatScroll.innerHTML = html;
}

function sendUserMessage(e) {
    e.preventDefault();
    var input = document.getElementById('userChatMessageInput');
    var btn = document.getElementById('userChatSendBtn');
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
            var bubbleHtml = '<div style="display: flex; flex-direction: column; align-items: flex-end;">' +
                '<div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 4px; padding: 0 4px;">Anda • ' + data.message.time + '</div>' +
                '<div style="max-width: 72%; padding: 12px 18px; border-radius: 16px; font-size: 0.92rem; line-height: 1.5; background: var(--primary); color: #ffffff; border-bottom-right-radius: 4px;">' +
                escapeHtml(data.message.message).replace(/\n/g, '<br>') +
                '</div></div>';
            chatScroll.insertAdjacentHTML('beforeend', bubbleHtml);
            scrollToBottom();
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        alert('Gagal mengirim pesan. Silakan coba lagi.');
    });
}
</script>
@endpush
