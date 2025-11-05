<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to {{ env('MAIL_FROM_NAME') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 20px;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e8e5ef;
            border-radius: 6px;
            padding: 40px;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #1a202c;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #343a40;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .button:hover {
            background-color: darkgrey;
            color: #fff;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <h1>Welcome to {{ env('MAIL_FROM_NAME') }}</h1>
            <p>Hi {{ Auth::user()->given_name }} {{ Auth::user()->family_name }},</p>
            <p>Thank you for registering with {{ env('MAIL_FROM_NAME') }}. To activate your account, please click the button below:</p>
            <p style="text-align: center; color: #fff;">
                <a href="{{ $url }}" class="button" style="color: #fff;">Activate Account</a>
            </p>
            <p>This activation link will expire in 60 minutes.</p>
            <p>Once you activate your account, you’ll be able to log in.</p>
            <p>If you have any questions, feel free to contact us at <a href="mailto:{{ env('MAIL_CONTACT') }}">{{ env('MAIL_CONTACT') }}</a>.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ env('MAIL_FROM_NAME') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
