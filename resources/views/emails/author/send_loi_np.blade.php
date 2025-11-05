<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Letter of Invitation – {{ $conference_name }}</title>
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
            <h1>Letter on Invitation – {{ $conference_name }}</h1>
            <p>Dear {{ $author_name }},</p>
            <p>We are pleased to inform you that we have received your registration payment as Non Presenter Participant for the {{ $conference_name }}. Please find attached your official Letter of Invitation (LoI) for participation in the {{ $conference_name }}, which will be held on {{ $conference_date }} at Politeknik Negeri Bandung, Indonesia. This letter may be used for visa application or other administrative purposes if required.</p>
            <p>We sincerely thank you for your contribution, and we look forward to welcoming you at Politeknik Negeri Bandung, Indonesia.</p>
            <p>Best regards,<br>{{ $conference_name }} Committee</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('mail.from.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>