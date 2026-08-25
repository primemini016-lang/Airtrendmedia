@php
    $errors = $errors ?? null;
@endphp
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm flex items-start gap-2">
    <span><x-icon name="check" class="w-4 h-4 inline" /></span><span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm flex items-start gap-2">
    <span><x-icon name="star" class="w-4 h-4 inline" /></span><span>{{ session('error') }}</span>
</div>
@endif
@if(session('info'))
<div class="mb-4 px-4 py-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-start gap-2">
    <span>ℹ</span><span>{{ session('info') }}</span>
</div>
@endif
@if(session('warning'))
<div class="mb-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start gap-2">
    <span><x-icon name="complaints" class="w-4 h-4 inline" /></span><span>{{ session('warning') }}</span>
</div>
@endif
@if($errors && $errors->any())
<div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
    <p class="font-semibold mb-1">Please fix the following:</p>
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif
