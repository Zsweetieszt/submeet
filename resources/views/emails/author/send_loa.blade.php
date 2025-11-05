<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Paper Accepted – {{ $conference_name }}</title>
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
            <h1>Paper Accepted – {{ $conference_name }}</h1>
            <p>Dear {{ $author_name }},</p>
            <p>We are pleased to inform you that your paper titled "{{ $paper_title }}" has been accepted for
                presentation at the {{ $conference_name }}, organized by {{ $organizer }}, to be held on
                {{ $conference_dates }} at Politeknik Negeri Bandung, Indonesia.</p>
            <p>Please take note of the following important information:</p>
            <ol>
                <li>Please upload the camera-ready version of your paper and your presentation slides to our system at
                    https://submeet.id</li>
                <li>Complete the registration and payment process through our submission system.</li>
                <li>Participants with Indonesian nationality are <strong>required</strong> to attend and present onsite (<strong>offline</strong>).</li>
                <li>Overseas participants (non-Indonesian nationality) may present online or onsite, based on their
                    preference.</li>
            </ol>
            <p>Further details regarding the presentation schedule will be provided in due course.</p>
            <p>Please find your LoA attached to this email for your reference.</p>
            <p>We thank you for your contribution to {{ $conference_name }} and look forward to your participation in
                the event.</p>
            <p>Best regards,<br>{{ $conference_name }} Committee</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('mail.from.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>