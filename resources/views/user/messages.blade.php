@extends('layouts.user')

@section('title', 'Support')
@section('heading', 'Support Messages')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card mb-4 bg-blue-50 border-blue-200">
        <div class="card-body text-sm text-slate-700">
            <p class="font-semibold text-blue-700"><x-icon name="messages" class="w-4 h-4 inline" /> Need help?</p>
            <p>Send a message to our support team and we'll get back to you as soon as possible. This is a private conversation between you and the site administrators.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div id="chatBox" class="space-y-3 max-h-96 overflow-y-auto min-h-[300px]">
                @if($messages->isEmpty())
                    <div class="text-center text-slate-400 py-12">
                        <div class="text-4xl mb-2"><x-icon name="messages" class="w-4 h-4 inline" /></div>
                        <p>No messages yet. Start the conversation below.</p>
                    </div>
                @else
                    @foreach($messages as $msg)
                        <div class="flex {{ $msg->from_admin ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-[80%] rounded-2xl px-4 py-2 {{ $msg->from_admin ? 'bg-slate-100 text-slate-800 rounded-bl-sm' : 'auth-gradient text-white rounded-br-sm' }}">
                                <p class="text-xs {{ $msg->from_admin ? 'text-slate-400' : 'text-blue-100' }} mb-1">{{ $msg->from_admin ? 'Support Team' : 'You' }} · {{ $msg->created_at->format('M d, H:i') }}</p>
                                <p class="text-sm whitespace-pre-wrap">{{ $msg->message }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.messages.send') }}" method="POST">
                @csrf
                <div class="flex gap-2">
                    <textarea name="message" class="input flex-1" rows="2" placeholder="Type your message…" required maxlength="3000"></textarea>
                    <button type="submit" class="btn btn-primary self-stretch">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const box = document.getElementById('chatBox');
    box.scrollTop = box.scrollHeight;
</script>
@endpush
@endsection
