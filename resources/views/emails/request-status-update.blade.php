<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Request Status Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .status { font-size: 18px; font-weight: bold; padding: 10px; margin: 10px 0; background: #e8f4fd; color: #004085; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Service Request Status Update</h1>
        </div>

        <div class="content">
            <p>Dear {{ $serviceRequest->citizen->username ?? 'Citizen' }},</p>

            <p>Your service request has been updated. Here are the details:</p>

            <div class="status">
                Status changed from "{{ $oldStatus }}" to "{{ $newStatus }}"
            </div>

            <h3>Request Details</h3>
            <ul>
                <li><strong>Request ID:</strong> #{{ $serviceRequest->id }}</li>
                <li><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'N/A' }}</li>
                <li><strong>Office:</strong> {{ $serviceRequest->service->office->name ?? 'N/A' }}</li>
                <li><strong>Submitted:</strong> {{ $serviceRequest->created_at?->format('M d, Y H:i') }}</li>
                <li><strong>Current Status:</strong> {{ $newStatus }}</li>
            </ul>

            @if($serviceRequest->qr_code)
                <p>Track your request with QR code: <strong>{{ $serviceRequest->qr_code }}</strong></p>
            @endif

            <p>If you have any questions, please contact the office directly.</p>

            <p>Best regards,<br>
            {{ $serviceRequest->service->office->name ?? config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
