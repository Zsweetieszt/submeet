<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Editorial Decision: Minor Revisions Required – {{ $conference_name }}</title>
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
        .feedback-section {
            background-color: #f8fafc;
            border: 1px solid #e8e5ef;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        .feedback-section h3 {
            font-size: 16px;
            color: #1a202c;
            margin: 0 0 10px 0;
        }
        .feedback-section p {
            margin: 8px 0;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-line;
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
            <h1>Editorial Decision: Minor Revisions Required – {{ $conference_name }}</h1>
            <p>Dear {{ $author_name }},</p>
            <p>Your paper titled "<strong>{{ $paper_title }}</strong>" has been reviewed and is recommended for <strong>minor revisions</strong>.</p>
            <p>The reviewers found your paper promising but have noted a few minor issues that need to be addressed before final acceptance, including:</p>
            @if(isset($similarity))
            <div style="margin-bottom:16px; padding:12px; border-radius:6px; background:#fffefa; border:1px solid #f0e6d8;">
                <strong>Similarity score:</strong> {{ $similarity }}%
            </div>
            @endif
            <div class="feedback-section">
                <p>{{ $combined_feedback }}</p>
            </div>
            
            @if($revision_deadline)
                <p>Please submit the revised version by <strong>{{ $revision_deadline }}</strong>.</p>
            @endif
            
            <p>Kind regards,<br>{{ $conference_name }} Editorial Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('mail.from.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>