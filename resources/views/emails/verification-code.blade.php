@extends('emails.layouts.master', ['headerSubtitle' => 'Verify your email address'])

@section('content')
<h1>Verify Your Account</h1>

<p>Hello <strong>{{ $user }}</strong>,</p>

<p>Thank you for registering with us! To complete your account verification and activate your account, please use the One-Time Password (OTP) below:</p>

<div class="otp-box">
    <div class="otp-label">Your Verification Code</div>
    <div class="otp-code">{{ $otp }}</div>
</div>

<div class="email-alert">
    <strong>Security Notice:</strong> This code is valid for a limited time and can only be used once. Never share this code with anyone — our team will never ask for it.
</div>

<p>If you did not create an account with us, you can safely ignore this email.</p>

<a href="{{ config('app.url', 'https://airtrendmedia.com') }}" class="email-button">Visit {{ config('app.name', 'Airtrendmedia') }}</a>

<p>Thanks,<br><strong>The {{ config('app.name', 'Airtrendmedia') }} Team</strong></p>
@endsection
