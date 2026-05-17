@extends('users.layout')

@section('title', 'Chat with ' . ($otherUser->username ?? 'Office'))

@push('scripts')
<style>
    .chat-container {
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .chat-header {
        padding: 1.5rem;
        border-bottom: 1px solid #eaeaea;
        background: #fafafa;
        border-radius: 1rem 1rem 0 0;
    }
    .chat-messages {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1.5rem;
        background: #fdfdfd;
    }
    .message-item {
        display: flex;
        flex-direction: column;
        margin-bottom: 1rem;
    }
    .message-item.sent {
        align-items: flex-end;
    }
    .message-item.received {
        align-items: flex-start;
    }
    .message-bubble {
        max-width: 70%;
        padding: 0.8rem 1rem;
        border-radius: 1rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        background: #0d6efd;
        color: #fff;
    }
    .message-sent {
        margin-left: auto;
    }
    .message-received {
        margin-right: auto;
    }
    .chat-input {
        padding: 1.5rem;
        border-top: 1px solid #eaeaea;
        background: #fff;
        border-radius: 0 0 1rem 1rem;
    }
    .file-attachment {
        display: inline-flex;
        align-items: center;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        margin-top: 0.5rem;
        color: inherit;
        text-decoration: none;
    }
    .file-attachment:hover {
        background: rgba(255,255,255,0.3);
        color: inherit;
    }
    .message-received .file-attachment {
        background: rgba(0,0,0,0.05);
    }
    .message-time { font-size: 0.7rem; opacity: 0.8; }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Scroll to bottom
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;

        $('#chatForm').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let submitBtn = $('#submitMsg');
            let originalText = submitBtn.html();
            
            if (!$('#messageInput').val().trim() && !$('#attachmentInput')[0].files.length) {
                return;
            }

            submitBtn.html('<i class="bi bi-hourglass-split"></i>').prop('disabled', true);

            $.ajax({
                url: $('#chatForm').data('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#messageInput').val('');
                    $('#attachmentInput').val('');

                    // Add message to UI
                    let msg = response.data;
                    let attachmentHtml = '';
                    if (msg.attachment) {
                        let fileName = msg.attachment.split('/').pop();
                        attachmentHtml = `<br><a href="/storage/${msg.attachment}" target="_blank" class="file-attachment"><i class="bi bi-paperclip me-2"></i>${fileName}</a>`;
                    }

                    let timeStr = 'Just now';
                    try {
                        timeStr = new Intl.DateTimeFormat('en-US', {hour:'numeric', minute:'numeric', hour12:true, timeZone:'Asia/Beirut'}).format(new Date(msg.created_at));
                    } catch(e) {}

                    let html = `
                        <div class="message-item sent">
                            <div class="message-bubble message-sent">
                                <div>${msg.message}</div>
                                ${attachmentHtml}
                                <div class="text-end mt-1 message-time">${timeStr}</div>
                            </div>
                        </div>
                    `;
                    $('#chatMessages').append(html);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    submitBtn.html(originalText).prop('disabled', false);
                },
                error: function(xhr) {
                    console.error('Chat send failed', xhr.status, xhr.responseText);
                    alert('Error sending message. Please try again.');
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
            return false;
        });
    });
</script>
@endpush

@section('content')
@php
    $office = \App\Models\Government_Offices::where('user_id', $otherUser->id)->first();
    $displayName = $office ? $office->name : $otherUser->username;
@endphp

<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="{{ route('user.chat.index') }}" class="text-decoration-none text-secondary"><i class="bi bi-arrow-left"></i> Back to Messages</a>
    </div>

    <div class="chat-container">
        <div class="chat-header d-flex align-items-center">
            <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold;">
                {{ strtoupper(substr($displayName, 0, 1)) }}
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">{{ $displayName }}</h5>
                <span class="text-success small"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Active now</span>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            @foreach($messages as $msg)
                @php
                    $isSent = $msg->sender_id === auth()->id();
                    $senderRole = $msg->sender->role ?? 'citizen';
                    $displayName = $isSent ? auth()->user()->username : ($msg->sender->username ?? 'User');
                    $avatarLabel = $isSent ? 'You' : 'Office';
                    $timeStr = $msg->created_at->timezone('Asia/Beirut')->format('h:i A');
                @endphp
                <div class="message-item {{ $isSent ? 'sent' : 'received' }}">
                    <div class="message-bubble message-sent {{ $isSent ? 'message-sent' : 'message-received' }}">
                        <div>{{ $msg->message }}</div>
                        @if($msg->attachment)
                            <br>
                            <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="file-attachment">
                                <i class="bi bi-paperclip me-2"></i> {{ basename($msg->attachment) }}
                            </a>
                        @endif
                        <div class="text-end mt-1 message-time" style="{{ $isSent ? 'opacity: 0.8;' : 'color: #8c98a4;' }}">{{ $timeStr }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="chat-input">
            <form id="chatForm" method="POST" action="javascript:void(0)" data-action="{{ route('chat.send') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
                <div class="input-group">
                    <label class="input-group-text bg-light border-0 cursor-pointer" for="attachmentInput" style="cursor: pointer;">
                        <i class="bi bi-paperclip fs-5 text-secondary"></i>
                    </label>
                    <input type="file" class="d-none" id="attachmentInput" name="attachment">
                    <input type="text" class="form-control border-0 bg-light py-3 px-4" id="messageInput" name="message" placeholder="Type your message here..." autocomplete="off">
                    <button class="btn btn-primary px-4" type="submit" id="submitMsg">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
