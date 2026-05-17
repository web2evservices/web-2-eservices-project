<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request Status Updated</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e3a5f; color: #fff; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>Request Status Updated</h1></div>
        <div class="content">
            <p>Hello,</p>
            <p>Request <strong>#{{ $serviceRequest->id }}</strong> changed from <strong>{{ $oldStatus }}</strong> to <strong>{{ $newStatus }}</strong>.</p>
            <ul>
                <li><strong>Citizen:</strong> {{ $serviceRequest->citizen->username ?? 'N/A' }}</li>
                <li><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'N/A' }}</li>
            </ul>
            <p>— {{ config('app.name') }}</p>
        </div>
        </div>
    </div>
</body>
</html>
