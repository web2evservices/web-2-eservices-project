<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Service Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .details { margin: 15px 0; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #007bff; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Service Request Received</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>A new service request has been submitted to your office.</p>

            <div class="details">
                <strong>Request Details:</strong><br>
                <strong>Request ID:</strong> #{{ $request->id }}<br>
                <strong>QR Code:</strong> {{ $request->qr_code }}<br>
                <strong>From:</strong> {{ $citizenName }}<br>
                <strong>Service:</strong> {{ $serviceName }}<br>
                <strong>Status:</strong> {{ $request->status }}<br>
                <strong>Submitted:</strong> {{ $request->created_at->format('Y-m-d H:i:s') }}
            </div>

            <p>Please log in to your office dashboard to review and process this request.</p>

            <p>Best regards,<br>
            E-Services Platform</p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
