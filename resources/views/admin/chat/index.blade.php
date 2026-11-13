@extends('layouts.admin')

@section('title', 'Chat Pelanggan - BookStore Admin')
@section('page_title', 'Chat Langsung Pelanggan')

@section('content')
<div class="card" style="border-radius: var(--radius-md); overflow: hidden; height: calc(100vh - 160px); min-height: 580px; display: flex; flex-direction: column;">
    <div style="display: grid; grid-template-columns: 320px 1fr; height: 100%; min-height: 0;">
        <!-- Left: Customers Conversation List -->
        <div style="border-right: 1px solid var(--border); display: flex; flex-direction: column; background: #fafbfc; min-height: 0;">
            <div style="padding: 16px 18px; border-bottom: 1px solid var(--border); background: #ffffff;">
                <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-main); margin-bottom: 8px;">
                    💬 Daftar Percakapan
                </div>
                <input type="text" id="chatSearchInput" placeholder="Cari nama atau email pembeli..." class="form-control text-xs" onkeyup="filterChatUsers()">
            </div>

            <div style="flex: 1; overflow-y: auto;" id="chatUsersList">
                @forelse($users as $u)
                    <a href="{{ route('admin.chat.index', ['user_id' => $u->id]) }}" 
                       class="chat-user-item {{ ($selectedUser && $selectedUser->id === $u->id) ? 'active' : '' }}"
                       data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}"
                       style="display: flex; gap: 12px; padding: 14px 18px; border-bottom: 1px solid var(--border-light); text-decoration: none; color: inherit; transition: background .15s;">
                        <div class="user-avatar" style="width: 42px; height: 42px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div class="d-flex align-center justify-between gap-1 mb-1">
                                <span style="font-weight: 700; font-size: 0.92rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $u->name }}
                                </span>
                                @if($u->last_message)
                                    <span class="text-xs text-muted" style="flex-shrink: 0; font-size: 0.72rem;">
                                        {{ $u->last_message->created_at->format('H:i') }}
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex align-center justify-between gap-1">
                                <span class="text-xs text-muted" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; max-width: 170px;">
                                    {{ $u->last_message ? Str::limit($u->last_message->message, 30) : 'Belum ada pesan' }}
                                </span>
                                @if($u->unread_count > 0)
                                    <span class="badge badge-primary" style="font-size: 0.68rem; padding: 2px 7px; border-radius: 999px;">
                                        {{ $u->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <!-- Show other customers if no chats yet -->
                    <div style="padding: 16px; font-size: 0.8rem; color: var(--text-muted); text-align: center;">
                        Belum ada pesan dari pelanggan.
                        <div class="mt-2 text-xs">Pilih pelanggan di bawah untuk mulai kirim pesan:</div>
                    </div>
                    @foreach($allCustomers as $u)
                        <a href="{{ route('admin.chat.index', ['user_id' => $u->id]) }}" 
                           class="chat-user-item {{ ($selectedUser && $selectedUser->id === $u->id) ? 'active' : '' }}"
                           style="display: flex; gap: 12px; padding: 12px 18px; border-bottom: 1px solid var(--border-light); text-decoration: none; color: inherit;">
                            <div class="user-avatar" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: var(--text-main); font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 700; font-size: 0.88rem; color: var(--text-main);">{{ $u->name }}</div>
                                <div class="text-xs text-muted">{{ $u->email }}</div>
                            </div>
                        </a>
                    @endforeach
                @endforelse
            </div>
        </div>

        <!-- Right: Active Chat Conversation Stream -->
        <div style="display: flex; flex-direction: column; height: 100%; min-height: 0; background: #ffffff;">
            @if($selectedUser)
                <!-- Chat Stream Header -->
                <div style="padding: 14px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
                    <div class="d-flex align-center gap-3">
                        <div class="user-avatar" style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary); color: #ffffff; font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">
                            {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 1rem; color: var(--text-main);">
                                {{ $selectedUser->name }}
                            </div>
                            <div class="text-xs text-muted">
                                📞 {{ $selectedUser->phone ?? '-' }} • ✉️ {{ $selectedUser->email }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('admin.customers.show', $selectedUser->id) }}" class="btn btn-sm btn-secondary" target="_blank">
                            👤 Profil Pembeli
                        </a>
                    </div>
                </div>

                <!-- Chat Messages Scroll Container -->
                <div style="flex: 1; overflow-y: auto; padding: 24px; background: #f8fafc; display: flex; flex-direction: column; gap: 14px;" id="adminChatMessagesContainer">
                    @forelse($messages as $msg)
                        <div class="chat-bubble-wrap {{ $msg->isFromAdmin() ? 'outgoing' : 'incoming' }}" style="display: flex; flex-direction: column; align-items: {{ $msg->isFromAdmin() ? 'flex-end' : 'flex-start' }};">
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 4px; padding: 0 4px;">
                                {{ $msg->isFromAdmin() ? 'Anda (Admin)' : $selectedUser->name }} • {{ $msg->created_at->format('H:i') }}
                            </div>
                            <div class="chat-bubble {{ $msg->isFromAdmin() ? 'bubble-admin' : 'bubble-customer' }}" 
                                 style="max-width: 70%; padding: 12px 18px; border-radius: 16px; font-size: 0.92rem; line-height: 1.5; {{ $msg->isFromAdmin() ? 'background: var(--primary); color: #ffffff; border-bottom-right-radius: 4px;' : 'background: #ffffff; color: var(--text-main); border: 1px solid var(--border); border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);' }}">
                                {!! nl2br(e($msg->message)) !!}
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                            <div style="font-size: 2.5rem; margin-bottom: 8px;">💬</div>
                            <div style="font-weight: 700;">Belum ada riwayat pesan dengan pembeli ini.</div>
                            <div class="text-xs mt-1">Ketikkan salam atau balasan pertama Anda pada formulir di bawah.</div>
                        </div>
                    @endforelse
                </div>

                <!-- Chat Input Reply Box -->
                <div style="padding: 18px 24px; border-top: 1px solid var(--border); background: #ffffff;">
                    <form id="adminChatSendForm" onsubmit="sendAdminMessage(event)" style="margin: 0; display: flex; gap: 12px; align-items: flex-end;">
                        @csrf
                        <div style="flex: 1;">
                            <textarea id="adminMessageInput" name="message" rows="2" class="form-control" placeholder="Tulis balasan untuk {{ $selectedUser->name }}... (Tekan Shift+Enter untuk baris baru)" style="resize: none; font-size: 0.92rem;" required></textarea>
                        </div>
                        <button type="submit" id="adminSendBtn" class="btn btn-primary" style="height: 52px; padding: 0 24px; font-weight: 700; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 8px;">
                            <span>Kirim</span>
                            <span>🚀</span>
                        </button>
                    </form>
                </div>
            @else
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 30px; color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: 12px;">💬</div>
                    <div style="font-size: 1.2rem; font-weight: 800; color: var(--text-main);">Pilih Percakapan Pelanggan</div>
                    <p style="max-width: 400px; font-size: 0.9rem; margin-top: 6px;">
                        Pilih salah satu pembeli pada daftar di sebelah kiri untuk melihat pesan dan mengirimkan balasan secara langsung.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.chat-user-item:hover { background-color: #f1f5f9; }
.chat-user-item.active { background-color: #eff6ff; border-left: 3px solid var(--primary); }
</style>
@endpush

@push('scripts')
@if($selectedUser)
<script>
var activeUserId = {{ $selectedUser->id }};
var chatContainer = document.getElementById('adminChatMessagesContainer');

function scrollToBottom() {
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
}
scrollToBottom();

// Poll for new messages every 4 seconds
var pollInterval = setInterval(function() {
    fetch('/admin/chat/' + activeUserId + '/messages', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success && data.messages) {
            renderMessages(data.messages);
        }
    })
    .catch(function(err) { console.error('Poll error', err); });
}, 4000);

function renderMessages(messages) {
    if (!chatContainer) return;
    var html = '';
    messages.forEach(function(msg) {
        var isOutgoing = msg.is_admin;
        html += '<div class="chat-bubble-wrap ' + (isOutgoing ? 'outgoing' : 'incoming') + '" style="display: flex; flex-direction: column; align-items: ' + (isOutgoing ? 'flex-end' : 'flex-start') + ';">';
        html += '<div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 4px; padding: 0 4px;">';
        html += (isOutgoing ? 'Anda (Admin)' : '{{ addslashes($selectedUser->name) }}') + ' • ' + msg.time;
        html += '</div>';
        html += '<div class="chat-bubble" style="max-width: 70%; padding: 12px 18px; border-radius: 16px; font-size: 0.92rem; line-height: 1.5; ' + (isOutgoing ? 'background: var(--primary); color: #ffffff; border-bottom-right-radius: 4px;' : 'background: #ffffff; color: var(--text-main); border: 1px solid var(--border); border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);') + '">';
        html += escapeHtml(msg.message).replace(/\n/g, '<br>');
        html += '</div></div>';
    });
    chatContainer.innerHTML = html;
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

function sendAdminMessage(e) {
    e.preventDefault();
    var input = document.getElementById('adminMessageInput');
    var btn = document.getElementById('adminSendBtn');
    var msg = input.value.trim();
    if (!msg) return;

    btn.disabled = true;

    fetch('/admin/chat/' + activeUserId + '/messages', {
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
            var bubbleHtml = '<div class="chat-bubble-wrap outgoing" style="display: flex; flex-direction: column; align-items: flex-end;">' +
                '<div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 4px; padding: 0 4px;">Anda (Admin) • ' + data.message.time + '</div>' +
                '<div class="chat-bubble" style="max-width: 70%; padding: 12px 18px; border-radius: 16px; font-size: 0.92rem; line-height: 1.5; background: var(--primary); color: #ffffff; border-bottom-right-radius: 4px;">' +
                escapeHtml(data.message.message).replace(/\n/g, '<br>') +
                '</div></div>';
            chatContainer.insertAdjacentHTML('beforeend', bubbleHtml);
            scrollToBottom();
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        alert('Gagal mengirim pesan. Silakan coba lagi.');
    });
}

function filterChatUsers() {
    var filter = document.getElementById('chatSearchInput').value.toLowerCase();
    var items = document.querySelectorAll('.chat-user-item');
    items.forEach(function(item) {
        var name = item.getAttribute('data-name') || '';
        var email = item.getAttribute('data-email') || '';
        if (name.includes(filter) || email.includes(filter)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
@endif
@endpush
