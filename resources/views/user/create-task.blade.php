@extends('layouts.user')

@section('title', 'Create Task')
@section('heading', 'Create a New Task')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card mb-5 bg-blue-50 border-blue-200">
        <div class="card-body text-sm text-slate-700">
            <p class="font-semibold text-blue-700 mb-1"><x-icon name="wallet" class="w-4 h-4 inline" /> How pricing works</p>
            <p>You pay the unit price × number of workers, plus a <strong>{{ $s->task_com }}%</strong> platform commission. Your wallet will be debited the total when the task is created. Tasks require admin approval before going live.</p>
            <p class="mt-1">Current balance: <strong>{{ money((float)auth('web')->user()->balance) }}</strong></p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.task.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="label">Task Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="input" value="{{ old('title') }}" placeholder="e.g. Subscribe to my YouTube channel" required maxlength="191">
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" class="input" required>
                            <option value="">Select category…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Time to complete (minutes)</label>
                        <input type="number" name="time" class="input" value="{{ old('time') }}" placeholder="e.g. 30" min="1" max="10080">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Reward per worker ($) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" id="price" class="input" value="{{ old('price') }}" placeholder="0.01" min="0.01" max="1000" step="0.01" required oninput="calcTotal()">
                    </div>
                    <div>
                        <label class="label">Number of workers <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="amount" class="input" value="{{ old('amount') }}" placeholder="10" min="1" max="1000" required oninput="calcTotal()">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="label">Action URL (where workers go to complete the task)</label>
                    <input type="url" name="action_url" class="input" value="{{ old('action_url') }}" placeholder="https://…">
                </div>

                <div class="mb-4">
                    <label class="label">Task Image</label>
                    <input type="file" name="image" accept="image/*" class="input" data-image-upload data-image-max-mb="8">
                    <p class="text-xs text-slate-500 mt-1">Attach a clear task image. Advertiser tasks without an image use the AI image generator when AI credits are available ({{ money((float) \App\Models\SiteSetting::get('ai_image_price', 0.20)) }} per image).</p>
                </div>

                <div class="mb-4">
                    <label class="label">Detailed Instructions <span class="text-red-500">*</span></label>
                    <textarea name="details" class="input" rows="6" required maxlength="10000" placeholder="Step-by-step instructions for workers. Explain exactly what they need to do and what proof you expect (screenshot, link, etc.).">{{ old('details') }}</textarea>
                </div>

                <div class="card bg-slate-50 mb-4">
                    <div class="card-body flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Estimated total cost</p>
                            <p class="text-xs text-slate-400">Includes {{ $s->task_com }}% commission</p>
                        </div>
                        <p id="totalDisplay" class="text-2xl font-bold text-blue-600">0.00</p>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full" onclick="return confirm('Confirm creating this task? The total amount will be deducted from your wallet.')">Create Task & Reserve Funds</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function calcTotal() {
        const price = parseFloat(document.getElementById('price').value) || 0;
        const amount = parseInt(document.getElementById('amount').value) || 0;
        const commission = {{ $s->task_com }};
        const total = (price * amount * (1 + commission / 100)).toFixed(2);
        document.getElementById('totalDisplay').textContent = total + '';
    }
</script>
@endpush
@endsection
