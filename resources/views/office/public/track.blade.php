<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; }
        .track-card { max-width: 520px; margin: 80px auto; }
    </style>
</head>
<body>
<div class="track-card">
    <div class="text-center mb-4">
        <i class="bi bi-building-fill text-primary fs-1"></i>
        <h4 class="mt-2 fw-bold">Government Services Portal</h4>
        <p class="text-muted">Request Status Tracker</p>
    </div>

    <div class="card border-0 shadow">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0"><i class="bi bi-qr-code-scan me-2"></i>Request #{{ $request->id }}</h5>
        </div>
        <div class="card-body p-4">
            @php
                $statusColors = [
                    'Pending'           => 'warning',
                    'In Review'         => 'info',
                    'Missing Documents' => 'danger',
                    'Approved'          => 'success',
                    'Rejected'          => 'danger',
                    'Completed'         => 'primary',
                ];
                $color = $statusColors[$request->status] ?? 'secondary';
            @endphp

            <div class="text-center mb-4">
                <span class="badge bg-{{ $color }} fs-6 px-4 py-2">{{ $request->status }}</span>
            </div>

            <table class="table table-sm table-borderless">
                <tr>
                    <th class="text-muted">Service</th>
                    <td>{{ $request->service->name }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Office</th>
                    <td>{{ $request->service->office->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Submitted</th>
                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Last Updated</th>
                    <td>{{ $request->updated_at->format('M d, Y h:i A') }}</td>
                </tr>
            </table>
        </div>
        <div class="card-footer bg-transparent text-center text-muted small">
            Scan the QR code to check your request status at any time.
        </div>
    </div>
</div>
</body>
</html>