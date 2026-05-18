<?php

namespace App\Http\Controllers\Office;

use App\Events\RequestStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequests;
use App\Models\Documents;
use App\Models\Government_Offices;
use App\Models\Office;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    private function governmentOfficeId(): int
    {
        $profile = Office::where('user_id', Auth::id())->firstOrFail();

        $governmentOffice = Government_Offices::where('user_id', Auth::id())->first();

        if (! $governmentOffice) {
            $governmentOffice = Government_Offices::create([
                'user_id'         => Auth::id(),
                'name'            => $profile->name,
                'address'         => $profile->address,
                'municipality_id' => $profile->municipality_id,
                'contact_info'    => $profile->contact_info ?? $profile->phone ?? '',
                'latitude'        => $profile->latitude ?? 0,
                'longitude'       => $profile->longitude ?? 0,
            ]);
        }

        return $governmentOffice->id;
    }

    public function index(Request $request)
    {
        $governmentOfficeId = $this->governmentOfficeId();

        $query = ServiceRequests::with(['service', 'citizen', 'documents'])
            ->whereHas('service', function ($q) use ($governmentOfficeId) {
                $q->where('office_id', $governmentOfficeId);
            });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('office.requests.index', compact('requests'));
    }

    public function show($id)
    {
        $governmentOfficeId = $this->governmentOfficeId();

        $request = ServiceRequests::with(['service', 'citizen', 'documents', 'requestHistories'])
            ->whereHas('service', function ($q) use ($governmentOfficeId) {
                $q->where('office_id', $governmentOfficeId);
            })
            ->findOrFail($id);

        return view('office.requests.show', compact('request'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,In Review,Missing Documents,Approved,Rejected,Completed',
        ]);

        $governmentOfficeId = $this->governmentOfficeId();

        $serviceRequest = ServiceRequests::whereHas('service', function ($q) use ($governmentOfficeId) {
            $q->where('office_id', $governmentOfficeId);
        })->findOrFail($id);

        $oldStatus = $serviceRequest->status;
        $newStatus = $validated['status'];

        // Status transition validation
        $allowedTransitions = [
            'Pending'           => ['In Review'],
            'In Review'         => ['Missing Documents', 'Approved', 'Rejected'],
            'Missing Documents' => ['In Review'],
            'Approved'          => ['Completed'],
            'Rejected'          => [],
            'Completed'         => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)) {
            return back()->withErrors(['status' => "Invalid status transition from '{$oldStatus}' to '{$newStatus}'."]);
        }

        $serviceRequest->status = $newStatus;
        $serviceRequest->save();

        // Dispatch event for request update notification
        RequestStatusUpdated::dispatch($serviceRequest, $oldStatus, $newStatus);

        // Log status change
        \App\Models\RequestHistories::create([
            'service_request_id' => $serviceRequest->id,
            'old_status'         => $oldStatus,
            'new_status'         => $newStatus,
            'changed_by'         => Auth::id(),
        ]);

        ActivityLogger::updated(
            'service_request',
            $serviceRequest->id,
            "Updated service request #{$serviceRequest->id} status from {$oldStatus} to {$newStatus}",
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );

        return back()->with('success', 'Request status updated successfully.');
    }

    public function uploadDocument(Request $request, $id)
    {
        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'type'     => 'required|string',
        ]);

        $governmentOfficeId = $this->governmentOfficeId();

        $serviceRequest = ServiceRequests::whereHas('service', function ($q) use ($governmentOfficeId) {
            $q->where('office_id', $governmentOfficeId);
        })->findOrFail($id);

        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('documents', $fileName, 'public');

        Documents::create([
            'service_request_id' => $serviceRequest->id,
            'document_type'      => $validated['type'],
            'file_path'          => $path,
        ]);

        if ($officeUserId = $office->user_id) {
            if ($officeUserId !== Auth::id()) {
                NotificationService::send(
                    $officeUserId,
                    'Request documents updated',
                    "Documents were attached to request #{$serviceRequest->id}.",
                    'request_documents'
                );
            }
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function downloadDocument($requestId, $documentId)
    {
        $governmentOfficeId = $this->governmentOfficeId();

        $serviceRequest = ServiceRequests::whereHas('service', function ($q) use ($governmentOfficeId) {
            $q->where('office_id', $governmentOfficeId);
        })->findOrFail($requestId);

        $document = Documents::where('id', $documentId)
            ->where('service_request_id', $serviceRequest->id)
            ->firstOrFail();

        $filePath = storage_path('app/public/' . $document->file_path);
        return response()->download($filePath);
    }

    /**
     * Generate a printable HTML summary (citizens can print to PDF from the browser).
     */
    public function generateSummary($id)
    {
        $governmentOfficeId = $this->governmentOfficeId();

        $serviceRequest = ServiceRequests::with(['service.office', 'citizen', 'requestHistories', 'documents'])
            ->whereHas('service', function ($q) use ($governmentOfficeId) {
                $q->where('office_id', $governmentOfficeId);
            })
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.request', ['request' => $serviceRequest]);
        $fileName = 'request_' . $serviceRequest->id . '_summary.pdf';
        $path     = 'documents/' . $fileName;

        Storage::disk('public')->put($path, $pdf->output());

        Documents::create([
            'service_request_id' => $serviceRequest->id,
            'document_type'      => 'generated',
            'file_path'          => $path,
        ]);

        return back()->with('success', 'PDF request summary generated. The citizen can download it from their documents list.');
    }
}