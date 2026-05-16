<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appointment Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #fd7e14; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .details { margin: 15px 0; padding: 15px; background-color: #fff3cd; border-left: 4px solid #fd7e14; }
        .alert { color: #856404; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Appointment Reminder - Tomorrow</h2>
        </div>
        <div class="content">
            <p class="alert">⏰ Reminder: You have an appointment scheduled for tomorrow!</p>

            <div class="details">
                <strong>Appointment Details:</strong><br>
                <strong>Citizen Name:</strong> {{ $appointment->citizen_name }}<br>
                <strong>Citizen Email:</strong> {{ $appointment->citizen_email }}<br>
                <strong>Citizen Phone:</strong> {{ $appointment->citizen_phone }}<br>
                <strong>Service:</strong> {{ $serviceName }}<br>
                <strong>Date & Time:</strong> {{ $appointment->date->format('Y-m-d H:i') }}<br>
                <strong>Time Slot:</strong> {{ $appointment->time_slot }}<br>
                <strong>Status:</strong> {{ $appointment->status }}<br>
                @if ($appointment->notes)
                    <strong>Notes:</strong> {{ $appointment->notes }}<br>
                @endif
            </div>

            <p>Please ensure you are available at the scheduled time to meet with the citizen.</p>

            <p>Best regards,<br>
            E-Services Platform</p>
        </div>
        <div class="footer">
            <p>This is an automated reminder message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
