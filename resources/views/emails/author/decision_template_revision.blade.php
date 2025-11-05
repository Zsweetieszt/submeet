<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Editorial Decision: Template Revision Required – {{ $conference_name }}</title>
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
            <h1>Editorial Decision: Template Revision Required – {{ $conference_name }}</h1>
            <p>Dear {{ $author_name }},</p>
            <p>Your paper titled "<strong>{{ $paper_title }}</strong>" has undergone the initial review, and the editorial board recommends "<strong>Template Revisions</strong>".</p>
            <p>Before we can proceed with the full review process, we kindly request that you revise your manuscript to comply with the official conference paper template. At this stage, we are unable to continue with content evaluation because the current formatting does not meet the required guidelines.</p>

            <p>Please address the following issues:</p>
            @if(isset($similarity))
            <div style="margin-bottom:16px; padding:12px; border-radius:6px; background:#fffefa; border:1px solid #f0e6d8;">
                <strong>Similarity score:</strong> {{ $similarity }}%
            </div>
            @endif
            <div class="feedback-section">
                <p>{{ $combined_feedback }}</p>
            </div>
            
            <p>Kindly submit your revised manuscript through the system no later than {{ $revision_deadline }}.</p>
        
            <p>We appreciate your cooperation and look forward to receiving your updated submission.</p>
            
            <p>Best regards,<br>{{ $conference_name }} Editorial Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('mail.from.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>