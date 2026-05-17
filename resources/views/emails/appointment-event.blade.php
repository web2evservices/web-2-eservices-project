<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f4f4f4; }
        .header { background: #1e3a5f; color: #fff; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #fff; padding: 20px; border-radius: 0 0 5px 5px; }
        .details { background: #f9f9f9; padding: 15px; border-left: 4px solid #1e3a5f; margin: 20px 0; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h2>{{ $heading }}</h2></div>
        <div class="content">
            <p>Dear {{ $recipientName }},</p>
            <p>{{ $body }}</p>
            <div class="details">
                <h3>Appointment details</h3>
                <p><strong>Service:</strong> {{ $serviceName }}</p>
                <p><strong>Office:</strong> {{ $officeName }}</p>
                <p><strong>Date:</strong> {{ $formattedDate }}</p>
                <p><strong>Time:</strong> {{ $formattedTime }}</p>
                <p><strong>Status:</strong> {{ $appointment->status }}</p>
                @if($appointment->notes)
                    <p><strong>Notes:</strong> {{ $appointment->notes }}</p>
                @endif
            </div>
            <div class="footer">
                <p>This is an automated message from {{ config('app.name') }}.</p>
            </div>
        </div>
    </div>
</body>
</html>
