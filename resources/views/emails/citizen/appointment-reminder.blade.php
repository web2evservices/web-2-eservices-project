<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: white; padding: 20px; border-radius: 0 0 5px 5px; }
        .appointment-details { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Appointment Reminder</h2>
        </div>
        <div class="content">
            <p>Dear {{ $appointment->citizen_name }},</p>
            
            <p>This is a reminder about your upcoming appointment.</p>
            
            <div class="appointment-details">
                <h3>Appointment Details:</h3>
                <p><strong>Service:</strong> {{ $serviceName }}</p>
                <p><strong>Office:</strong> {{ $officeName }}</p>
                <p><strong>Date:</strong> {{ $appointment->date->format('l, F j, Y') }}</p>
                <p><strong>Time:</strong> {{ $appointment->date->format('H:i') }}</p>
                @if ($appointment->notes)
                    <p><strong>Notes:</strong> {{ $appointment->notes }}</p>
                @endif
            </div>
            
            <p>Please make sure to arrive on time. If you need to reschedule or cancel, please contact the office as soon as possible.</p>
            
            <div class="footer">
                <p>This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
