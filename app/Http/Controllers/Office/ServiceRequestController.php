<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequests;
use App\Models\Documents;
use App\Models\Office;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $query = ServiceRequests::with(['service', 'citizen', 'documents'])
            ->whereHas('service', function ($q) use ($office) {
                $q->where('office_id', $office->id);
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
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $request = ServiceRequests::with(['service', 'citizen', 'documents', 'requestHistories'])
            ->whereHas('service', function ($q) use ($office) {
                $q->where('office_id', $office->id);
            })
            ->findOrFail($id);

        return view('office.requests.show', compact('request'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,In Review,Missing Documents,Approved,Rejected,Completed',
        ]);

        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $serviceRequest = ServiceRequests::whereHas('service', function ($q) use ($office) {
            $q->where('office_id', $office->id);
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

        // Log status change
        \App\Models\RequestHistories::create([
            'service_request_id' => $serviceRequest->id,
            'old_status'         => $oldStatus,
            'new_status'         => $newStatus,
            'changed_by'         => Auth::id(),
        ]);

        NotificationService::send(
            $serviceRequest->citizen_id,
            'Request status updated',
            "Your request #{$serviceRequest->id} status changed from {$oldStatus} to {$newStatus}.",
            'request_status'
        );

        NotificationService::sendToAdmins(
            'Request status changed',
            "Request #{$serviceRequest->id} status changed from {$oldStatus} to {$newStatus}.",
            'admin_activity'
        );

        if ($newStatus === 'Missing Documents') {
            NotificationService::send(
                $serviceRequest->citizen_id,
                'Documents required for request',
                "Your request #{$serviceRequest->id} requires additional documents. Please upload the requested files to continue processing.",
                'document_required'
            );
        }

        // Send email notification
        try {
            \Mail::to($serviceRequest->citizen->email)->send(
                new \App\Mail\RequestStatusUpdateMail($serviceRequest, $oldStatus, $newStatus)
            );
        } catch (\Exception $e) {
            // Log email sending failure but don't fail the request
            \Log::error('Failed to send status update email: ' . $e->getMessage());
        }

        return back()->with('success', 'Request status updated successfully.');
    }

    public function uploadDocument(Request $request, $id)
    {
        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'type'     => 'required|string',
        ]);

        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $serviceRequest = ServiceRequests::whereHas('service', function ($q) use ($office) {
            $q->where('office_id', $office->id);
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
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $serviceRequest = ServiceRequests::whereHas('service', function ($q) use ($office) {
            $q->where('office_id', $office->id);
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
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $serviceRequest = ServiceRequests::with(['service.office', 'citizen', 'requestHistories', 'documents'])
            ->whereHas('service', function ($q) use ($office) {
                $q->where('office_id', $office->id);
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