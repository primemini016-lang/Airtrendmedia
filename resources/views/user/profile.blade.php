@extends('layouts.user')

@section('title', 'Profile')
@section('heading', 'My Profile')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <!-- Avatar + summary -->
    <div class="card">
        <div class="card-body text-center">
            @if($user->image)
                <img src="{{ asset('storage/'.$user->image) }}" class="w-28 h-28 rounded-full object-cover mx-auto mb-3 border-4 border-blue-100">
            @else
                <div class="w-28 h-28 rounded-full auth-gradient flex items-center justify-center text-white text-4xl font-bold mx-auto mb-3">{{ strtoupper(substr($user->name,0,1)) }}</div>
            @endif
            <h3 class="font-bold text-slate-800">{{ $user->name }}</h3>
            <p class="text-slate-400 text-sm">{{ $user->username }}</p>
            <div class="mt-3">
                @if($user->is_active)
                    <span class="badge badge-success"><x-icon name="check" class="w-4 h-4 inline" /> Active Account</span>
                @else
                    <span class="badge badge-warning">Pending Activation</span>
                @endif
            </div>

            <form action="{{ route('user.profile.image') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                @csrf
                <label class="btn btn-outline text-sm cursor-pointer">
                    Change Photo
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.submit()">
                </label>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100 text-left space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Email</span><span class="text-slate-700 truncate ml-2">{{ $user->email }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Phone</span><span class="text-slate-700">{{ $user->phone ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Referral</span><span class="text-slate-700 font-mono">{{ $user->referral_code }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Joined</span><span class="text-slate-700">{{ $user->created_at->format('M d, Y') }}</span></div>
            </div>
        </div>
    </div>

    <!-- Edit form -->
    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Edit Profile</h3>
            <form action="{{ route('user.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div>
                        <label class="label">Username</label>
                        <input type="text" class="input bg-slate-50" value="{{ $user->username }}" disabled>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Email</label>
                        <input type="email" class="input bg-slate-50" value="{{ $user->email }}" disabled>
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" name="phone" class="input" value="{{ old('phone', $user->phone) }}">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="label">Country</label>
                    <select name="country_code" class="input">
                        <option value="">Select country…</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->code }}" {{ old('country_code', $user->country_code) == $c->code ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="label">Bio</label>
                    <textarea name="bio" class="input" rows="4" maxlength="2000">{{ old('bio', $user->bio) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
