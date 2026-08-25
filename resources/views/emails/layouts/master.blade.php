<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $subject ?? config('app.name', 'Airtrendmedia') }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f1f5f9;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 32px 16px;
        }
        .email-container {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(37, 99, 235, 0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            padding: 36px 40px;
            text-align: center;
        }
        .email-header .logo {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .email-header .logo .accent {
            color: #93c5fd;
        }
        .email-header .header-subtitle {
            font-size: 13px;
            color: #bfdbfe;
            margin-top: 6px;
            font-weight: 500;
        }
        .email-body {
            padding: 40px;
            color: #334155;
            font-size: 15px;
            line-height: 1.7;
        }
        .email-body h1 {
            color: #1e3a8a;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .email-body h2 {
            color: #2563eb;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .email-body p {
            margin-bottom: 16px;
            color: #475569;
        }
        .email-body strong {
            color: #1e293b;
        }
        .otp-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px dashed #2563eb;
            border-radius: 12px;
            padding: 28px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            color: #1e40af;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .otp-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .email-button {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            margin: 16px 0 24px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        }
        .email-alert {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 20px 0;
            font-size: 14px;
            color: #92400e;
        }
        .email-alert strong { color: #78350f; }
        .email-footer {
            background-color: #1e293b;
            padding: 28px 40px;
            text-align: center;
        }
        .email-footer p {
            color: #94a3b8;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .email-footer .footer-links {
            margin-bottom: 12px;
        }
        .email-footer .footer-links a {
            color: #60a5fa;
            text-decoration: none;
            font-size: 13px;
            margin: 0 10px;
        }
        .email-footer .copyright {
            color: #64748b;
            font-size: 12px;
            margin-top: 12px;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 24px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-body { padding: 24px 20px; }
            .email-header { padding: 28px 20px; }
            .email-footer { padding: 20px; }
            .otp-code { font-size: 28px; letter-spacing: 4px; }
            .email-button { padding: 12px 28px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <div class="logo">{{ config('app.name', 'Airtrendmedia') }}</div>
                <div class="header-subtitle">{{ $headerSubtitle ?? 'Your trusted microjob marketplace' }}</div>
            </div>
            <div class="email-body">
                @yield('content')
            </div>
            <div class="email-footer">
                <div class="footer-links">
                    <a href="{{ config('app.url', 'https://airtrendmedia.com') }}">Website</a>
                    <a href="{{ config('app.url', 'https://airtrendmedia.com') }}/contact">Support</a>
                    <a href="{{ config('app.url', 'https://airtrendmedia.com') }}/terms">Terms</a>
                </div>
                <p>If you have any questions, just reply to this email.</p>
                <p class="copyright">&copy; {{ date('Y') }} {{ config('app.name', 'Airtrendmedia') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
