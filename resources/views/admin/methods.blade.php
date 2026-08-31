@extends('layouts.admin')

@section('title', 'Payment Methods')
@section('heading', 'Payment Methods')

@section('content')
<div class="grid lg:grid-cols-2 gap-6">
    <!-- Deposit methods -->
    <div>
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">Add Deposit Method</h3>
                <form action="{{ route('admin.methods.deposit.store') }}" method="POST">
                    @csrf
                    <div class="mb-3"><label class="label">Name <span class="text-red-500">*</span></label><input type="text" name="name" class="input" placeholder="Bank Transfer" required></div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div><label class="label">Min Amount</label><input type="number" name="min_amount" class="input" step="0.01" value="0"></div>
                        <div><label class="label">Position</label><input type="number" name="position" class="input" value="0"></div>
                    </div>
                    <div class="mb-3"><label class="label">Instructions</label><textarea name="instructions" class="input" rows="2" placeholder="Payment instructions for users"></textarea></div>
                    <div class="space-y-2 mb-4">
                        <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="active" value="1" checked class="rounded border-slate-300 text-blue-600 mr-2"> Active</label>
                        <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="manual" value="1" class="rounded border-slate-300 text-blue-600 mr-2"> Manual (admin confirms payment)</label>
                    </div>
                    <button class="btn btn-primary w-full">Add Method</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-3">Deposit Methods ({{ $depositMethods->total() }})</h3>
                @forelse($depositMethods as $m)
                    <div class="border border-slate-200 rounded-lg p-3 mb-2">
                        <form action="{{ route('admin.methods.deposit.update', $m) }}" method="POST">@csrf
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <input type="text" name="name" class="input text-sm" value="{{ $m->name }}" required>
                                <input type="number" name="min_amount" class="input text-sm" value="{{ $m->min_amount }}" step="0.01">
                            </div>
                            <textarea name="instructions" class="input text-sm mb-2" rows="2">{{ $m->instructions }}</textarea>
                            <input type="hidden" name="position" value="{{ $m->position }}">
                            <input type="hidden" name="active" value="{{ $m->active ? 1 : 0 }}">
                            <input type="hidden" name="manual" value="{{ $m->manual ? 1 : 0 }}">
                            <div class="flex items-center justify-between">
                                <div class="flex gap-2 text-xs">
                                    @if($m->active)<span class="badge badge-success">Active</span>@else<span class="badge badge-muted">Inactive</span>@endif
                                    @if($m->manual)<span class="badge badge-info">Manual</span>@endif
                                </div>
                                <div class="flex gap-2">
                                    <button class="btn btn-primary text-xs">Save</button>
                                    <form action="{{ route('admin.methods.deposit.delete', $m) }}" method="POST">@csrf @method('DELETE')<button class="text-red-600 text-xs hover:underline" onclick="return confirm('Delete?')">Delete</button></form>
                                </div>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-400 text-center py-6 text-sm">No deposit methods.</p>
                @endforelse
                {{ $depositMethods->links() }}
            </div>
        </div>
    </div>

    <!-- Withdrawal methods -->
    <div>
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">Add Withdrawal Method</h3>
                <form action="{{ route('admin.methods.withdrawal.store') }}" method="POST">
                    @csrf
                    <div class="mb-3"><label class="label">Name <span class="text-red-500">*</span></label><input type="text" name="name" class="input" placeholder="PayPal / Bank /T" required></div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div><label class="label">Min Amount</label><input type="number" name="min_amount" class="input" step="0.01" value="0"></div>
                        <div><label class="label">Position</label><input type="number" name="position" class="input" value="0"></div>
                    </div>
                    <div class="space-y-2 mb-4">
                        <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="active" value="1" checked class="rounded border-slate-300 text-blue-600 mr-2"> Active</label>
                        <label class="flex items-center text-sm text-slate-600"><input type="checkbox" name="gift_card" value="1" class="rounded border-slate-300 text-blue-600 mr-2"> Gift card option</label>
                    </div>
                    <button class="btn btn-primary w-full">Add Method</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-3">Withdrawal Methods ({{ $withdrawalMethods->total() }})</h3>
                @forelse($withdrawalMethods as $m)
                    <div class="border border-slate-200 rounded-lg p-3 mb-2">
                        <form action="{{ route('admin.methods.withdrawal.update', $m) }}" method="POST">@csrf
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <input type="text" name="name" class="input text-sm" value="{{ $m->name }}" required>
                                <input type="number" name="min_amount" class="input text-sm" value="{{ $m->min_amount }}" step="0.01">
                            </div>
                            <input type="hidden" name="position" value="{{ $m->position }}">
                            <input type="hidden" name="active" value="{{ $m->active ? 1 : 0 }}">
                            <input type="hidden" name="gift_card" value="{{ $m->gift_card ? 1 : 0 }}">
                            <div class="flex items-center justify-between">
                                <div class="flex gap-2 text-xs">
                                    @if($m->active)<span class="badge badge-success">Active</span>@else<span class="badge badge-muted">Inactive</span>@endif
                                    @if($m->gift_card)<span class="badge badge-info">Gift Card</span>@endif
                                </div>
                                <div class="flex gap-2">
                                    <button class="btn btn-primary text-xs">Save</button>
                                    <form action="{{ route('admin.methods.withdrawal.delete', $m) }}" method="POST">@csrf @method('DELETE')<button class="text-red-600 text-xs hover:underline" onclick="return confirm('Delete?')">Delete</button></form>
                                </div>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-400 text-center py-6 text-sm">No withdrawal methods.</p>
                @endforelse
                {{ $withdrawalMethods->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
