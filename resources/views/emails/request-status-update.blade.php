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
        .status { font-size: 18px; font-weight: bold; padding: 10px; margin: 10px 0; }
        .status.pending { background-color: #fff3cd; color: #856404; }
        .status.in-review { background-color: #cce5ff; color: #004085; }
        .status.approved { background-color: #d4edda; color: #155724; }
        .status.rejected { background-color: #f8d7da; color: #721c24; }
        .status.completed { background-color: #d1ecf1; color: #0c5460; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Service Request Status Update</h1>
        </div>

        <div class="content">
            <p>Dear {{ $request->citizen->name ?? 'Citizen' }},</p>

            <p>Your service request has been updated. Here are the details:</p>

            <div class="status status-{{ strtolower(str_replace(' ', '-', $newStatus)) }}">
                Status changed from "{{ $oldStatus }}" to "{{ $newStatus }}"
            </div>

            <h3>Request Details:</h3>
            <ul>
                <li><strong>Request ID:</strong> {{ $request->id }}</li>
                <li><strong>Service:</strong> {{ $request->service->name }}</li>
                <li><strong>Office:</strong> {{ $request->service->office->name }}</li>
                <li><strong>Submitted:</strong> {{ $request->created_at->format('M d, Y H:i') }}</li>
                <li><strong>Current Status:</strong> {{ $request->status }}</li>
            </ul>

            @if($request->qr_code)
                <p>You can track your request status using this QR code: {{ $request->qr_code }}</p>
            @endif

            <p>If you have any questions, please contact the office directly.</p>

            <p>Best regards,<br>
            {{ $request->service->office->name }} Team</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>