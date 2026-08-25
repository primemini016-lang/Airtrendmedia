@extends('layouts.admin')

@section('title', 'Settings')
@section('heading', 'Site Settings')

@section('content')
<form action="{{ route('admin.settings') }}" method="POST">
    @csrf
    <div class="grid lg:grid-cols-2 gap-6">

        <!-- General -->
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">General</h3>
                <div class="mb-3"><label class="label">Site Name <span class="text-red-500">*</span></label><input type="text" name="name" class="input" value="{{ $s->name }}" required></div>
                <div class="mb-3"><label class="label">Logo Text</label><input type="text" name="logotext" class="input" value="{{ $s->logotext }}"></div>
                <div class="mb-3"><label class="label">Site URL</label><input type="text" name="url" class="input" value="{{ $s->url }}"></div>
                <div class="mb-3"><label class="label">Default Currency <span class="text-red-500">*</span></label>
                    <select name="default_currency_id" class="input" required>
                        @foreach($currencies as $c)<option value="{{ $c->id }}" {{ $s->default_currency_id == $c->id ? 'selected' : '' }}>{{ $c->code }} — {{ $c->name }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">Contact</h3>
                <div class="mb-3"><label class="label">Contact Email</label><input type="email" name="contact_email" class="input" value="{{ $s->contact_email }}"></div>
                <div class="mb-3"><label class="label">Phone</label><input type="text" name="phone" class="input" value="{{ $s->phone }}"></div>
                <div class="mb-3"><label class="label">Address</label><input type="text" name="address" class="input" value="{{ $s->address }}"></div>
                <div class="mb-3"><label class="label">Footer Text</label><input type="text" name="footer_text" class="input" value="{{ $s->footer_text }}"></div>
            </div>
        </div>

        <!-- Financial -->
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">Financial</h3>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="label">Activation Fee (USD) <span class="text-red-500">*</span></label><input type="number" name="activation_fee" class="input" value="{{ $s->activation_fee }}" step="0.01" required></div>
                    <div><label class="label">Affiliate Reward (USD) <span class="text-red-500">*</span></label><input type="number" name="affiliate_reward" class="input" value="{{ $s->affiliate_reward }}" step="0.01" required></div>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="label">Task Commission (%) <span class="text-red-500">*</span></label><input type="number" name="task_com" class="input" value="{{ $s->task_com }}" step="0.01" required></div>
                    <div><label class="label">Withdrawal Fee (%) <span class="text-red-500">*</span></label><input type="number" name="withdraw_com" class="input" value="{{ $s->withdraw_com }}" step="0.01" required></div>
                </div>
                <div class="space-y-2">
                    <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="affiliate_enabled" value="1" {{ $s->affiliate_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 mr-2"> Enable affiliate program</label>
                    <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="manual_payment" value="1" {{ $s->manual_payment ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 mr-2"> Allow manual payment confirmation</label>
                </div>
            </div>
        </div>

        <!-- Appearance & misc -->
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">Appearance & Misc</h3>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="label">Primary Color</label><input type="text" name="primary_color" class="input" value="{{ $s->primary_color }}" placeholder="#2563eb"></div>
                    <div><label class="label">Accent Color</label><input type="text" name="accent_color" class="input" value="{{ $s->accent_color }}" placeholder="#1e40af"></div>
                </div>
                <div class="mb-3"><label class="label">Announcement Text</label><input type="text" name="ann_text" class="input" value="{{ $s->ann_text }}"></div>
                <div class="space-y-2">
                    <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="ann_status" value="1" {{ $s->ann_status ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 mr-2"> Show announcement bar</label>
                    <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="need_verification" value="1" {{ $s->need_verification ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 mr-2"> Require email verification on registration</label>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <button class="btn btn-primary px-8">Save All Settings</button>
    </div>
</form>
@endsection
