@extends('layouts.admin')

@section('title', 'Conversation')
@section('heading', 'Conversation with ' . $user->name)

@section('content')
<div class="mb-4"><a href="{{ route('admin.messages') }}" class="text-blue-600 hover:underline text-sm">← Back to Messages</a></div>

<div class="max-w-3xl mx-auto">
    <div class="card mb-4">
        <div class="card-body">
            <div id="chatBox" class="space-y-3 max-h-96 overflow-y-auto min-h-[300px]">
                @if($messages->isEmpty())
                    <div class="text-center text-slate-400 py-12"><div class="text-4xl mb-2">💬</div><p>No messages in this conversation.</p></div>
                @else
                    @foreach($messages as $msg)
                        <div class="flex {{ $msg->from_admin ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-2xl px-4 py-2 {{ $msg->from_admin ? 'auth-gradient text-white rounded-br-sm' : 'bg-slate-100 text-slate-800 rounded-bl-sm' }}">
                                <p class="text-xs {{ $msg->from_admin ? 'text-blue-100' : 'text-slate-400' }} mb-1">{{ $msg->from_admin ? 'You (Admin)' : $user->name }} · {{ $msg->created_at->format('M d, H:i') }}</p>
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
            <form action="{{ route('admin.messages.show', $user) }}" method="POST">
                @csrf
                <div class="flex gap-2">
                    <textarea name="message" class="input flex-1" rows="2" placeholder="Type your reply…" required maxlength="3000"></textarea>
                    <button type="submit" class="btn btn-primary self-stretch">Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>document.getElementById('chatBox').scrollTop = document.getElementById('chatBox').scrollHeight;</script>
@endpush
@endsection
