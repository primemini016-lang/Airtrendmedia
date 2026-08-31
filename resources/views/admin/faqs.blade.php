@extends('layouts.admin')

@section('title', 'FAQs')
@section('heading', 'FAQ Management')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Add FAQ</h3>
            <form action="{{ route('admin.faqs') }}" method="POST">
                @csrf
                <div class="mb-3"><label class="label">Question <span class="text-red-500">*</span></label><input type="text" name="question" class="input" required></div>
                <div class="mb-3"><label class="label">Answer <span class="text-red-500">*</span></label><textarea name="answer" class="input" rows="4" required></textarea></div>
                <div class="mb-4"><label class="label">Position</label><input type="number" name="position" class="input" value="0"></div>
                <button class="btn btn-primary w-full">Add FAQ</button>
            </form>
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">FAQs ({{ $faqs->total() }})</h3>
            @if($faqs->isEmpty())<p class="text-slate-400 text-center py-8">No FAQs yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($faqs as $faq)
                        <div class="border border-slate-200 rounded-lg p-3">
                            <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">@csrf
                                <div class="mb-2"><input type="text" name="question" class="input" value="{{ $faq->question }}" required></div>
                                <div class="mb-2"><textarea name="answer" class="input" rows="2" required>{{ $faq->answer }}</textarea></div>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="position" class="input w-24" value="{{ $faq->position }}">
                                    <button class="btn btn-primary text-xs">Save</button>
                                </div>
                            </form>
                            <form action="{{ route('admin.faqs.delete', $faq) }}" method="POST" class="mt-2">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete this FAQ?')">Delete FAQ</button></form>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $faqs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
