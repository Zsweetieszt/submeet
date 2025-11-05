<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paper Submission Confirmation – {{ $conference_name }}</title>
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
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.2s;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #9ca3af;
            color: #ffffff !important;
        }
        .paper-id {
            background-color: #f8fafc;
            border: 1px solid #e8e5ef;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .paper-id strong {
            font-size: 18px;
            color: #1a202c;
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
            <h1>Paper Submission Confirmation – {{ $conference_name }}</h1>
            <p>Dear {{ $author_name }},</p>
            <p>Thank you for submitting your paper titled "<strong>{{ $paper_title }}</strong>" to {{ $conference_name }}.</p>
            <p>Your submission has been received successfully and is currently under initial review.</p>
            <div class="paper-id">
                <strong>Paper ID: {{ $first_paper_sub_id }}</strong>
            </div>
            <p>Best regards,<br>{{ $conference_name }} Editorial Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ env('MAIL_FROM_NAME') }}. All rights reserved.
        </div>
    </div>
</body>
</html>