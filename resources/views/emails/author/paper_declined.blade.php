<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paper Submission Outcome – {{ $conference_name }}</title>
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
        .decline-reasons {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #991b1b;
        }
        * {
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <h1>Paper Submission Outcome – {{ $conference_name }}</h1>
            <p>Dear {{ $author_name }},</p>
            <p>Thank you for submitting your paper titled "<strong>{{ $paper_title }}</strong>" to {{ $conference_name }}.</p>
            <p>After an initial review, we regret to inform you that your paper has been <strong>declined</strong> during the desk evaluation stage.</p>
            
            @if($decline_reasons)
                <p>The editorial decision was based on the following key considerations:</p>
                <div class="decline-reasons">
                    {{ $decline_reasons }}
                </div>
            @endif
            
            <p>We appreciate your interest in {{ $conference_name }} and encourage you to consider submitting to future editions.</p>
            
            <p>Kind regards,<br>
            {{ $conference_name }} Editorial Team</p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} SubMeet Conference Management System. All rights reserved.
        </div>
    </div>
</body>
</html>