@extends('layouts.user')
@section('title', $task->title)
@section('heading', 'Task Details')

@section('content')
<a href="{{ route('user.tasks') }}" class="text-blue-600 text-sm hover:underline mb-4 inline-block"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back to browse</a>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card"><div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <span class="badge badge-info">{{ $task->category?->name ?? 'General' }}</span>
                <span class="badge badge-success">{{ $task->booked }}/{{ $task->amount }} booked</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">{{ $task->title }}</h2>
            <p class="text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $task->details }}</p>
            <div class="mt-4 pt-4 border-t border-slate-100"><x-social-share :url="url()->current()" :title="$task->title" /></div>
            @if($task->action_url)
            <div class="mt-4 px-4 py-3 rounded-lg bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-400 mb-1">Action URL</p>
                <a href="{{ $task->action_url }}" target="_blank" class="text-blue-600 hover:underline break-all text-sm">{{ $task->action_url }} <x-icon name="arrow-right" class="w-4 h-4 inline" /></a>
            </div>
            @endif
        </div></div>

        @if($myProof)
        <div class="card"><div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Your Submitted Proof</h3>
            @php $statusLabels = [0=>['Pending Review','badge-warning'],1=>['Approved & Paid','badge-success'],2=>['Rejected','badge-danger'],3=>['Disputed','badge-warning']]; $sl = $statusLabels[(int)$myProof->status] ?? ['Unknown','badge-muted']; @endphp
            <p><span class="badge {{ $sl[1] }}">{{ $sl[0] }}</span></p>
            <p class="text-slate-600 text-sm mt-3 whitespace-pre-wrap">{{ $myProof->comment }}</p>
            @if($myProof->reject_note)<p class="text-red-600 text-sm mt-2"><strong>Rejection reason:</strong> {{ $myProof->reject_note }}</p>@endif
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">
                @foreach($myProof->images ?? [] as $img)<img src="{{ storage_asset($img) }}" class="rounded-lg w-full h-28 object-cover">@endforeach
            </div>
        </div></div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="card"><div class="card-body">
            <p class="text-slate-400 text-sm">Reward per task</p>
            <p class="text-3xl font-bold text-blue-600 mb-4">{{ money((float)$task->price) }}</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Slots</span><span class="font-semibold">{{ $task->booked }}/{{ $task->amount }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Posted by</span><span class="font-semibold"><a href="{{ $task->user ? route('user.public-profile', $task->user) : '#' }}" class="text-blue-600 hover:underline font-semibold">{{ $task->user?->username }}@if($task->user?->isBlueVerified()) <x-verified-badge size="w-4 h-4" class="align-middle" />@endif</a></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Posted on</span><span class="font-semibold">{{ $task->date->format('M d, Y') }}</span></div>
                @if($task->time)<div class="flex justify-between"><span class="text-slate-500">Est. time</span><span class="font-semibold">{{ $task->time }} min</span></div>@endif
            </div>

            @if($myBooking && !$myProof)
            <form action="{{ route('user.task', $task) }}/proof" method="POST" enctype="multipart/form-data" class="mt-5 space-y-3">
                @csrf
                <div>
                    <label class="label">Proof Comment</label>
                    <textarea name="comment" rows="3" class="input" required placeholder="Describe what you did..."></textarea>
                </div>
                <div>
                    <label class="label">Proof Images (1–5) <span class="text-red-500">*</span></label>
                    <input id="proofImages" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="input" required>
                    <div id="proofPreview" class="grid grid-cols-3 gap-2 mt-2"></div>
                    <p class="text-xs text-slate-400">Upload 1–5 clear screenshots/photos showing your completed work. Each image must be 3MB or less.</p>
                </div>
                <button class="btn btn-primary w-full">Submit Proof</button>
            </form>
            @elseif(!$myBooking && $task->booked < $task->amount && $task->isActive())
            <form action="{{ route('user.task', $task) }}/book" method="POST" class="mt-5">
                @csrf
                <button class="btn btn-primary w-full">Apply Now</button>
            </form>
            @elseif($myBooking && $myProof)
            <p class="mt-5 text-sm text-slate-400 text-center">Proof already submitted.</p>
            @elseif(!$task->isActive())
            <p class="mt-5 text-sm text-slate-400 text-center">This task is no longer active.</p>
            @else
            <p class="mt-5 text-sm text-slate-400 text-center">All slots filled.</p>
            @endif
        </div></div>
    </div>
</div>
@push('scripts')
<script>
(function(){
    const input=document.getElementById('proofImages'), preview=document.getElementById('proofPreview');
    if(!input||!preview)return;
    input.addEventListener('change',function(){
        preview.innerHTML='';
        const files=Array.from(input.files||[]);
        if(files.length>5){ input.value=''; alert('Please select no more than 5 proof images.'); return; }
        files.forEach(function(file){
            if(!file.type.startsWith('image/')) return;
            const img=document.createElement('img'); img.className='w-full h-24 object-cover rounded-lg border';
            img.alt='Proof preview'; const reader=new FileReader();
            reader.onload=e=>img.src=e.target.result; reader.readAsDataURL(file); preview.appendChild(img);
        });
    });
})();
</script>
@endpush
@endsection
