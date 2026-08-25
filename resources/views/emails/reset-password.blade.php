@extends('emails.layouts.master', ['headerSubtitle' => 'Password changed successfully'])

@section('content')
<h1>Password Changed</h1>

<p>Hello <strong>{{ $user }}</strong>,</p>

<p>This is a confirmation that the password on your <strong>{{ config('app.name', 'Airtrendmedia') }}</strong> account has been successfully changed.</p>

<p>If you made this change, no further action is required — you can now log in with your new password.</p>

<div class="email-alert">
    <strong>Did you not make this change?</strong> If you did <strong>not</strong> authorize this change, please contact our support team immediately so we can secure your account.
</div>

<a href="{{ config('app.url', 'https://airtrendmedia.com') }}" class="email-button">Log in to {{ config('app.name', 'Airtrendmedia') }}</a>

<p>Stay secure,<br><strong>The {{ config('app.name', 'Airtrendmedia') }} Team</strong></p>
@endsection
