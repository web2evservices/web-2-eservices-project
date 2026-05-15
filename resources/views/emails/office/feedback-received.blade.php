<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Feedback Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .details { margin: 15px 0; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #28a745; }
        .rating { font-size: 24px; color: #ffc107; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Feedback Received</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>A citizen has submitted feedback for one of your services.</p>

            <div class="details">
                <strong>Feedback Details:</strong><br>
                <strong>Service Request ID:</strong> #{{ $feedback->service_request_id }}<br>
                <strong>From:</strong> {{ $citizenName }}<br>
                <strong>Service:</strong> {{ $serviceName }}<br>
                <strong>Rating:</strong> <span class="rating">{{ str_repeat('★', $feedback->rating) }}{{ str_repeat('☆', 5 - $feedback->rating) }}</span> ({{ $feedback->rating }}/5)<br>
                <strong>Comment:</strong><br>
                <p style="margin: 10px 0;">{{ $feedback->comment ?? 'No comment provided' }}</p>
                <strong>Submitted:</strong> {{ $feedback->created_at->format('Y-m-d H:i:s') }}
            </div>

            <p>You can respond to this feedback through your office dashboard.</p>

            <p>Best regards,<br>
            E-Services Platform</p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
