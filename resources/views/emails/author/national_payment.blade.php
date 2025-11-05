<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Information – {{ $conference_name }}</title>
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
            <h1>Payment Information – {{ $conference_name }}</h1>
            <p>Dear {{ $list_author }},</p>
            <p>Thank you for your participation as a {{ $role }} at the upcoming {{ $conference_name }} Conference. We are pleased to confirm your acceptance and look forward to your contribution.</p>
            <p>Please find below the details of your payment as a conference {{ $role }}:</p>
            <div style="border: 1px solid #e8e5ef; border-radius: 6px; padding: 20px; background-color: #f9fafb;">
            <h2 style="font-size: 18px; margin-bottom: 15px; color: #1a202c;">Payment Details</h2>
            <p><strong>Amount:</strong> {{ $payment_currency }} {{ $payment_amount }} *</p>
            <p><strong>Payment Deadline:</strong> {{ $payment_end_date }}</p>
            <p><strong>Payment Purpose:</strong> {{ $conference_short_name }} {{ $conference_year }} Conference National {{ $role }} Fee ({{ $attendance_mode }})</p>
            <p><strong>BRI Virtual Account code:</strong> {{ $briva_number }}</p>
            <p style="font-size: 14px; color: #6b7280;">*This amount does not include any applicable bank transfer or administrative fees.</p>
            </div>
            <div style="margin-top: 20px;">
            <h2 style="font-size: 18px; margin-bottom: 15px; color: #1a202c;">Payment Instructions</h2>
            <ol style="font-size: 16px; line-height: 1.5; padding-left: 20px;">
                <li>Please ensure the exact amount ({{ $payment_currency }} {{ $payment_amount }}) is transferred before the deadline.</li>
                <li>Any additional fees incurred from bank transfers are the responsibility of the sender.</li>
                <li>BRIVA payments can be made either through BRI's Virtual Account service or by transferring funds from another bank to the designated BRIVA account number, with BRI as the receiving bank.</li>
                <li>After making the payment, kindly confirm your payment by sending proof of transfer via "My Payments" menu on submeet.id</li>
            </ol>
            </div>
            <p>Should you have any questions or require further assistance, feel free to reach out to us at issat@polban.ac.id .</p>
            <p>We look forward to seeing you at {{ $conference_short_name }} {{ $conference_year }}.</p>
            <p>Best regards,<br>{{ $conference_name }} Committee</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('mail.from.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>