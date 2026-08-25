@extends('layouts.admin')

@section('title', 'Currencies')
@section('heading', 'Currencies')

@section('content')
<div class="card mb-4 bg-blue-50 border-blue-200">
    <div class="card-body text-sm text-slate-700">
        <p class="font-semibold text-blue-700 mb-1">💵 How the multi-currency system works</p>
        <p>Each currency has a <strong>USD value</strong> (how many units equal 1 USD). Users see prices in the default currency, but Paystack only accepts local currencies (NGN, GHS, ZAR, KES). Mark currencies that Paystack supports with the checkbox. When a user pays, the USD amount is converted to a Paystack-supported currency automatically.</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Add Currency</h3>
            <form action="{{ route('admin.currencies') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="label">Code <span class="text-red-500">*</span></label><input type="text" name="code" class="input" placeholder="NGN" required maxlength="6"></div>
                    <div><label class="label">Symbol <span class="text-red-500">*</span></label><input type="text" name="symbol" class="input" placeholder="₦" required maxlength="8"></div>
                </div>
                <div class="mb-3"><label class="label">Name <span class="text-red-500">*</span></label><input type="text" name="name" class="input" placeholder="Nigerian Naira" required></div>
                <div class="mb-3"><label class="label">USD Value (units per 1 USD) <span class="text-red-500">*</span></label><input type="number" name="usd_value" class="input" placeholder="1500.00" step="0.000001" required></div>
                <div class="mb-3"><label class="label">Position</label><input type="number" name="position" class="input" value="0"></div>
                <div class="space-y-2 mb-4">
                    <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="is_default" value="1" class="rounded border-slate-300 text-blue-600 mr-2"> Set as default currency</label>
                    <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="paystack_supported" value="1" class="rounded border-slate-300 text-blue-600 mr-2"> Paystack-supported</label>
                    <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="active" value="1" checked class="rounded border-slate-300 text-blue-600 mr-2"> Active</label>
                </div>
                <button class="btn btn-primary w-full">Add Currency</button>
            </form>
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Active Currencies</h3>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Code</th><th>Name</th><th>Symbol</th><th>USD Value</th><th>Default</th><th>Paystack</th><th></th></tr></thead>
                    <tbody>
                        @forelse($currencies as $c)
                            <tr>
                                <td class="font-bold text-slate-800">{{ $c->code }}</td>
                                <td>{{ $c->name }}</td>
                                <td class="text-lg">{{ $c->symbol }}</td>
                                <td class="font-semibold">{{ number_format($c->usd_value,4) }}</td>
                                <td>@if($c->is_default)<span class="badge badge-success">Default</span>@else<span class="text-slate-300">—</span>@endif</td>
                                <td>@if($c->paystack_supported)<span class="badge badge-info">✓</span>@else<span class="text-slate-300">—</span>@endif</td>
                                <td>
                                    <form action="{{ route('admin.currencies.update', $c) }}" method="POST" class="inline">@csrf
                                        <input type="hidden" name="name" value="{{ $c->name }}"><input type="hidden" name="symbol" value="{{ $c->symbol }}">
                                        <input type="hidden" name="usd_value" value="{{ $c->usd_value }}"><input type="hidden" name="position" value="{{ $c->position }}">
                                        <input type="hidden" name="active" value="{{ $c->active ? 0 : 1 }}">
                                        <button class="text-xs {{ $c->active ? 'text-amber-600' : 'text-green-600' }} hover:underline">{{ $c->active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    @if(!$c->is_default)<form action="{{ route('admin.currencies.delete', $c) }}" method="POST" class="inline">@csrf@method('DELETE')<button class="text-xs text-red-600 hover:underline" onclick="return confirm('Delete this currency?')">Delete</button></form>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-slate-400 py-8">No currencies yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $currencies->links() }}</div>
        </div>
    </div>
</div>
@endsection
