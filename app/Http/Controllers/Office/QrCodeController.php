<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Government_Offices;
use App\Models\ServiceRequests;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;

class QrCodeController extends Controller
{
    public function show($requestId)
    {
        $office = Government_Offices::where('user_id', Auth::id())->firstOrFail();
        $serviceRequest = ServiceRequests::with(['service', 'citizen'])->findOrFail($requestId);

        // Security: only this office's requests
        if (!$serviceRequest->service || $serviceRequest->service->office_id !== $office->id) {
            abort(403);
        }

        // The QR encodes a public tracking URL using the unique qr_code string
        $trackingUrl = route('requests.track', $serviceRequest->qr_code);

        $qrSvg = QrCode::format('svg')->size(250)->generate($trackingUrl);

        return view('office.qrcode.show', compact('serviceRequest', 'qrSvg'));
    }

    public function download($requestId)
    {
        $office = Government_Offices::where('user_id', Auth::id())->firstOrFail();
        $serviceRequest = ServiceRequests::with('service')->findOrFail($requestId);

        if (!$serviceRequest->service || $serviceRequest->service->office_id !== $office->id) {
            abort(403);
        }

        $trackingUrl = route('requests.track', $serviceRequest->qr_code);
        $qrSvg = QrCode::format('svg')->size(300)->generate($trackingUrl);

        return response($qrSvg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="request-' . $serviceRequest->qr_code . '.svg"');
    }
}