<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request #{{ $request->id }} Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; }
        .container { width: 100%; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .section { margin-bottom: 16px; }
        .section h2 { font-size: 16px; border-bottom: 1px solid #ddd; padding-bottom: 6px; }
        .field { margin-bottom: 8px; }
        .field strong { display: inline-block; width: 180px; }
        .small { color: #666; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <p class="small" style="background:#f8f9fa;padding:8px;border:1px solid #ddd;">Open the generated PDF in a browser or PDF viewer to print or save it.</p>
    <div class="container">
        <div class="header">
            <h1>Service Request Summary</h1>
            <p class="small">Request ID: {{ $request->id }} | Status: {{ $request->status }}</p>
        </div>

        <div class="section">
            <h2>Request Details</h2>
            <div class="field"><strong>Service:</strong> {{ $request->service->name ?? 'N/A' }}</div>
            <div class="field"><strong>Office:</strong> {{ $request->service->office->name ?? 'N/A' }}</div>
            <div class="field"><strong>Requested by:</strong> {{ $request->citizen->username ?? 'Citizen' }}</div>
            <div class="field"><strong>Submitted:</strong> {{ $request->created_at->format('Y-m-d H:i') }}</div>
            <div class="field"><strong>Appointment ID:</strong> {{ $request->appointment_id ?? 'Not provided' }}</div>
            <div class="field"><strong>QR Code:</strong> {{ $request->qr_code }}</div>
        </div>

        <div class="section">
            <h2>Workflow History</h2>
            @if($request->requestHistories->count())
                <table class="table">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Changed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->requestHistories as $history)
                            <tr>
                                <td>{{ $history->old_status }}</td>
                                <td>{{ $history->new_status }}</td>
                                <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="small">No history records yet.</p>
            @endif
        </div>

        <div class="section">
            <h2>Documents</h2>
            @if($request->documents->count())
                <ul>
                    @foreach($request->documents as $document)
                        <li>{{ $document->document_type }} - {{ basename($document->file_path) }}</li>
                    @endforeach
                </ul>
            @else
                <p class="small">No documents uploaded.</p>
            @endif
        </div>
    </div>
</body>
</html>
