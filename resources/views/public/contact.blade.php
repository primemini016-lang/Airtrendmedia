@extends('layouts.app')
@section('title', 'Contact — Microjob Marketplace')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-slate-800 mb-2 text-center">Contact Us</h1>
    <p class="text-slate-500 text-center mb-8">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                @csrf
                <div class="mb-4">
                    <label class="label">Your Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input" required>
                </div>
                <div class="mb-4">
                    <label class="label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input" required>
                </div>
                <div class="mb-4">
                    <label class="label">Message</label>
                    <textarea name="message" rows="5" class="input" required>{{ old('message') }}</textarea>
                </div>
                <button class="btn btn-primary w-full">Send Message</button>
            </form>
        </div>
    </div>

    @if(!empty($contactEmail) || !empty($phone) || !empty($address))
    <div class="grid sm:grid-cols-3 gap-4 mt-6">
        @if(!empty($contactEmail))<div class="card p-4 text-center"><div class="text-2xl mb-1">✉️</div><p class="text-xs text-slate-400">Email</p><p class="text-sm font-semibold text-slate-700">{{ $contactEmail }}</p></div>@endif
        @if(!empty($phone))<div class="card p-4 text-center"><div class="text-2xl mb-1">📞</div><p class="text-xs text-slate-400">Phone</p><p class="text-sm font-semibold text-slate-700">{{ $phone }}</p></div>@endif
        @if(!empty($address))<div class="card p-4 text-center"><div class="text-2xl mb-1">📍</div><p class="text-xs text-slate-400">Address</p><p class="text-sm font-semibold text-slate-700">{{ $address }}</p></div>@endif
    </div>
    @endif
</div>
@endsection
