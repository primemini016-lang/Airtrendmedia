@extends('emails.layouts.master', ['headerSubtitle' => 'Password reset request'])

@section('content')
<h1>Reset Your Password</h1>

<p>Hello <strong>{{ $user }}</strong>,</p>

<p>We received a request to reset the password on your account. Use the verification code below to proceed with resetting your password:</p>

<div class="otp-box">
    <div class="otp-label">Your Reset Code</div>
    <div class="otp-code">{{ $otp }}</div>
</div>

<div class="email-alert">
    <strong>Security Notice:</strong> This code is valid for a limited time and can only be used once. Never share this code with anyone.
</div>

<p>If you did not request a password reset, please ignore this email — your account remains secure and no changes have been made.</p>

<a href="{{ config('app.url', 'https://airtrendmedia.com') }}" class="email-button">Return to {{ config('app.name', 'MiniWorkers') }}</a>

<p>Stay safe,<br><strong>The {{ config('app.name', 'MiniWorkers') }} Team</strong></p>
@endsection
