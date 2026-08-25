@extends('layouts.admin')

@section('title', 'Email Settings')
@section('heading', 'Email / SMTP Settings')

@section('content')
<div class="mb-6">
    <p class="text-slate-500 text-sm">Configure your SMTP settings so emails reach users' inbox (not spam). Use a reputable SMTP provider like Mailgun, SendGrid, Amazon SES, or your host's SMTP.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">SMTP Configuration</h3>
            <form action="{{ route('admin.email-settings.update') }}" method="POST">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="mb-3">
                        <label class="label">Mail Driver</label>
                        <select name="mail_mailer" class="input">
                            @foreach(['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'mailgun' => 'Mailgun', 'ses' => 'Amazon SES', 'log' => 'Log (debug)'] as $val => $label)
                                <option value="{{ $val }}" {{ ($config['mail_mailer'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="label">Encryption</label>
                        <select name="mail_encryption" class="input">
                            <option value="tls" {{ ($config['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($config['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="null" {{ ($config['mail_encryption'] ?? '') === null ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="label">SMTP Host</label>
                        <input type="text" name="mail_host" class="input" value="{{ $config['mail_host'] ?? '' }}" placeholder="smtp.gmail.com">
                    </div>
                    <div class="mb-3">
                        <label class="label">SMTP Port</label>
                        <input type="number" name="mail_port" class="input" value="{{ $config['mail_port'] ?? '587' }}" placeholder="587">
                    </div>
                    <div class="mb-3">
                        <label class="label">SMTP Username</label>
                        <input type="text" name="mail_username" class="input" value="{{ $config['mail_username'] ?? '' }}" placeholder="you@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="label">SMTP Password</label>
                        <input type="password" name="mail_password" class="input" value="{{ $config['mail_password'] ?? '' }}" placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="label">From Address <span class="text-red-500">*</span></label>
                        <input type="email" name="mail_from_address" class="input" value="{{ $config['mail_from_address'] ?? '' }}" required placeholder="noreply@airtrendmedia.com">
                    </div>
                    <div class="mb-3">
                        <label class="label">From Name <span class="text-red-500">*</span></label>
                        <input type="text" name="mail_from_name" class="input" value="{{ $config['mail_from_name'] ?? '' }}" required placeholder="AirtrendMedia">
                    </div>
                </div>
                <button class="btn btn-primary">Save Email Settings</button>
            </form>

            <hr class="my-6 border-slate-200">

            <h4 class="font-bold text-slate-800 mb-2">Registration Email Verification</h4>
            <p class="text-sm text-slate-500 mb-3">By default new users are <strong>auto-activated and auto-logged-in</strong> on registration (no email code needed). Enable the option below <em>after</em> your SMTP is configured if you want to require users to verify their email address with a code before they can log in.</p>
            <form action="{{ route('admin.email-settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="mail_mailer" value="{{ $config['mail_mailer'] ?? 'smtp' }}">
                <input type="hidden" name="mail_from_address" value="{{ $config['mail_from_address'] ?? 'noreply@airtrendmedia.com' }}">
                <input type="hidden" name="mail_from_name" value="{{ $config['mail_from_name'] ?? 'AirtrendMedia' }}">
                <label class="flex items-center text-sm text-slate-700 gap-2"><input type="checkbox" name="need_verification" value="1" {{ ($appSetting->need_verification ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600"> Require email verification code on registration</label>
                <button class="btn btn-outline mt-3">Save Verification Setting</button>
            </form>

            <hr class="my-6 border-slate-200">

            <h4 class="font-bold text-slate-800 mb-3">Send Test Email</h4>
            <form action="{{ route('admin.email-settings.test') }}" method="POST" class="flex gap-2 flex-wrap items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="label">Test Email Address</label>
                    <input type="email" name="test_email" class="input" placeholder="you@example.com" required>
                </div>
                <button class="btn btn-outline">Send Test</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Avoid Spam Tips</h3>
            <ul class="text-sm text-slate-600 space-y-3">
                <li class="flex gap-2"><span class="text-blue-600"><x-icon name="check" class="w-4 h-4 inline" /></span> Use a real domain email (not gmail/yahoo for sending)</li>
                <li class="flex gap-2"><span class="text-blue-600"><x-icon name="check" class="w-4 h-4 inline" /></span> Set up SPF, DKIM, and DMARC DNS records</li>
                <li class="flex gap-2"><span class="text-blue-600"><x-icon name="check" class="w-4 h-4 inline" /></span> Use a reputable SMTP provider (Mailgun, SES, SendGrid)</li>
                <li class="flex gap-2"><span class="text-blue-600"><x-icon name="check" class="w-4 h-4 inline" /></span> Keep "From" name consistent</li>
                <li class="flex gap-2"><span class="text-blue-600"><x-icon name="check" class="w-4 h-4 inline" /></span> Include unsubscribe options in marketing emails</li>
                <li class="flex gap-2"><span class="text-blue-600"><x-icon name="check" class="w-4 h-4 inline" /></span> Test deliverability before bulk sending</li>
            </ul>
        </div>
    </div>
</div>
@endsection
