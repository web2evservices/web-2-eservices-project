<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Message</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #17a2b8; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .message-box { margin: 15px 0; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #17a2b8; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Message</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>You have received a new message from {{ $senderName }}.</p>

            <div class="message-box">
                <strong>From:</strong> {{ $senderName }}<br>
                @if ($message->service_request_id)
                    <strong>Service Request ID:</strong> #{{ $message->service_request_id }}<br>
                @endif
                <strong>Received:</strong> {{ $message->created_at->format('Y-m-d H:i:s') }}<br><br>
                <strong>Message:</strong><br>
                <p>{{ $message->message }}</p>
                @if ($message->attachment)
                    <p><strong>Attachment:</strong> File attached</p>
                @endif
            </div>

            <p>Please log in to your office dashboard to reply to this message.</p>

            <p>Best regards,<br>
            E-Services Platform</p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
