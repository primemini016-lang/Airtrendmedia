<div class="card">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">Apply for Verification</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Upload a government ID. {{ money((float)$monthlyFee) }} will be deducted from your wallet balance (current: {{ money((float)$userBalance) }}).</p>

        @if($userBalance < $monthlyFee)
            <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 mb-4">
                <p class="text-sm text-red-600 dark:text-red-400">Insufficient balance. You need at least {{ money((float)$monthlyFee) }} in your wallet. <a href="{{ route('user.wallet') }}" class="underline font-semibold">Top up now →</a></p>
            </div>
        @endif

        <form action="{{ route('user.verification.apply') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Full Legal Name *</label>
                    <input type="text" name="full_name" required class="form-input" placeholder="John Doe" value="{{ old('full_name') }}">
                </div>
                <div>
                    <label class="form-label">Category *</label>
                    <select name="category" required class="form-input">
                        <option value="">Select...</option>
                        <option value="individual" @selected(old('category')==='individual')>Individual / Person</option>
                        <option value="business" @selected(old('category')==='business')>Business</option>
                        <option value="creator" @selected(old('category')==='creator')>Content Creator</option>
                        <option value="public_figure" @selected(old('category')==='public_figure')>Public Figure</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Government ID Document *</label>
                <input type="file" name="government_id" required accept="image/*" class="form-input text-sm" onchange="previewId(this)">
                <img id="prev-gov-id" class="mt-2 max-h-40 rounded-lg hidden">
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn btn-primary" @if($userBalance < $monthlyFee) disabled @endif>
                    Pay {{ money((float)$monthlyFee) }} & Apply
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewId(input) {
    const img = document.getElementById('prev-gov-id');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => { img.src = e.target.result; img.classList.remove('hidden'); };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
