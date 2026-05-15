<?php

namespace App\Http\Controllers;

use App\Events\ServiceRequestCreated;
use App\Models\Documents;
use App\Models\RequestHistories;
use App\Models\ServiceRequests;
use App\Models\Services;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    // ---------------------------------------------------------------------------
    // Temporary helper — remove ?? 1 fallback when auth is live
    // ---------------------------------------------------------------------------
    private function actingAsId(): int
    {
        return Auth::id() ?? 1;
    }

    // ---------------------------------------------------------------------------
    // Citizen-facing endpoints
    // ---------------------------------------------------------------------------

    public function index()
    {
        $requests = ServiceRequests::with(['service.office', 'documents', 'requestHistories'])
            ->where('citizen_id', $this->actingAsId())
            ->get();

        return response()->json(['data' => $requests]);
    }

    public function show($id)
    {
        $request = ServiceRequests::with(['service.office', 'documents', 'requestHistories'])
            ->where('id', $id)
            ->where('citizen_id', $this->actingAsId())
            ->firstOrFail();

        return response()->json(['data' => $request]);
    }

    public function pageIndex()
    {
        $services = Services::with('office')->get();
        $requests = ServiceRequests::with(['service.office', 'documents', 'requestHistories'])
            ->where('citizen_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('users.requests.index', compact('services', 'requests'));
    }

    public function pageCreate(Request $request)
    {
        $serviceId = $request->query('service_id');
        $service = null;

        if ($serviceId) {
            $service = Services::with(['office', 'category'])->findOrFail($serviceId);
        }

        $services = Services::with(['office', 'category'])->get();

        return view('users.requests.create', compact('service', 'services'));
    }

    public function pageShow($id)
    {
        return view('users.requests.show', compact('id'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'     => 'required|integer|exists:services,id',
            'appointment_id' => 'nullable|integer|exists:appointments,id',
            'documents'      => 'nullable|array',
            'documents.*.file' => 'required_with:documents|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'documents.*.type' => 'required_with:documents|string',
        ]);

        $serviceRequest = ServiceRequests::create([
            'citizen_id'     => $this->actingAsId(),
            'service_id'     => $validated['service_id'],
            'status'         => 'Pending',
            'qr_code'        => (string) Str::uuid(),
            'appointment_id' => $validated['appointment_id'] ?? null,
        ]);

        // Dispatch event to notify office
        ServiceRequestCreated::dispatch($serviceRequest);

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $index => $file) {
                if ($file) {
                    $documentType = $request->input("documents.{$index}.type", 'uploaded');
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('documents', $fileName, 'public');

                    Documents::create([
                        'service_request_id' => $serviceRequest->id,
                        'document_type'      => $documentType,
                        'file_path'          => $path,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Request created successfully',
            'data'    => $serviceRequest,
        ], 201);
    }

    // ---------------------------------------------------------------------------
    // Admin-facing endpoints
    // ---------------------------------------------------------------------------

    public function adminIndex(Request $request)
    {
        $query = ServiceRequests::with(['documents', 'requestHistories']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $requests]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $newStatus = $this->resolveStatus($validated['status']);

        if ($newStatus === null) {
            return response()->json(['message' => 'Invalid status value.'], 422);
        }

        $serviceRequest = ServiceRequests::findOrFail($id);
        $oldStatus      = $serviceRequest->status;

        if ($newStatus === $oldStatus) {
            return response()->json([
                'message' => 'Status is already set to this value.',
                'data'    => $serviceRequest,
            ]);
        }

        $allowedTransitions = [
            'Pending'           => ['In Review'],
            'In Review'         => ['Missing Documents', 'Approved', 'Rejected'],
            'Missing Documents' => ['In Review'],
            'Approved'          => ['Completed'],
            'Rejected'          => [],
            'Completed'         => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)) {
            return response()->json([
                'message' => "Invalid status transition from '{$oldStatus}' to '{$newStatus}'.",
            ], 400);
        }

        $serviceRequest->status = $newStatus;
        $serviceRequest->save();

        $historyData = [
            'service_request_id' => $serviceRequest->id,
            'old_status'         => $oldStatus,
            'new_status'         => $newStatus,
        ];

        if (Schema::hasColumn('request_histories', 'changed_by')) {
            $historyData['changed_by'] = $this->actingAsId();
        }

        RequestHistories::create($historyData);

        return response()->json([
            'message' => 'Status updated successfully',
            'data'    => $serviceRequest,
        ]);
    }

    public function generatePdf($id)
    {
        $serviceRequest = ServiceRequests::where('id', $id)
            ->where('citizen_id', $this->actingAsId())
            ->with(['service.office', 'citizen', 'requestHistories', 'documents'])
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.request', ['request' => $serviceRequest]);
        $fileName = 'request_' . $serviceRequest->id . '_summary.pdf';
        $path     = 'documents/' . $fileName;

        Storage::disk('public')->put($path, $pdf->output());

        $document = Documents::create([
            'service_request_id' => $serviceRequest->id,
            'document_type'      => 'generated',
            'file_path'          => $path,
        ]);

        return response()->json([
            'message' => 'PDF summary generated successfully.',
            'data' => $document,
            'download_url' => route('user.requests.documents.download', [
                'requestId' => $serviceRequest->id,
                'documentId' => $document->id,
            ]),
        ]);
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function resolveStatus(string $raw): ?string
    {
        $normalized = preg_replace('/(?<!^)([A-Z])/', ' $1', $raw);
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $normalized)));

        $map = [
            'pending'           => 'Pending',
            'in review'         => 'In Review',
            'missing documents' => 'Missing Documents',
            'approved'          => 'Approved',
            'rejected'          => 'Rejected',
            'completed'         => 'Completed',
        ];

        return $map[$normalized] ?? null;
    }
}
