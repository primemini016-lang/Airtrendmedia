@extends('layouts.app')
@section('title', 'FAQs — Microjob Marketplace')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-slate-800 mb-2 text-center">Frequently Asked Questions</h1>
    <p class="text-slate-500 text-center mb-8">Everything you need to know about earning on our marketplace.</p>

    @if($faqs->isNotEmpty())
    <div class="space-y-3">
        @foreach($faqs as $faq)
        <details class="card group">
            <summary class="card-body cursor-pointer flex items-center justify-between font-semibold text-slate-800 list-none">
                <span>{{ $faq->question }}</span>
                <span class="text-blue-600 transition group-open:rotate-45 text-xl">+</span>
            </summary>
            <div class="px-5 pb-5 text-slate-600 text-sm leading-relaxed">{{ $faq->answer }}</div>
        </details>
        @endforeach
    </div>
    @else
    <div class="card p-10 text-center text-slate-400">No FAQs yet. Please check back later.</div>
    @endif

    <div class="mt-10 text-center">
        <p class="text-slate-500">Still have questions?</p>
        <a href="{{ route('contact') }}" class="btn btn-primary mt-2">Contact Support</a>
    </div>
</div>
@endsection
