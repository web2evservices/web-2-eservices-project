<?php
namespace App\Http\Controllers;

use App\Models\Feddback;
use App\Models\ServiceRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    // Citizen submits feedback
    public function store(Request $request, $requestId)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $serviceRequest = ServiceRequests::where('id', $requestId)
            ->where('citizen_id', Auth::id())
            ->where('status', 'Completed')
            ->firstOrFail();

        $existing = Feddback::where('service_request_id', $serviceRequest->id)
            ->where('citizen_id', Auth::id())
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Feedback already submitted.'], 409);
        }

        $feedback = Feddback::create([
            'service_request_id' => $serviceRequest->id,
            'citizen_id'         => Auth::id(),
            'rating'             => $validated['rating'],
            'comment'            => $validated['comment'] ?? null,
        ]);

        return response()->json(['message' => 'Feedback submitted', 'data' => $feedback], 201);
    }

    // Office responds to feedback
    public function respond(Request $request, $feedbackId)
    {
        $validated = $request->validate(['response' => 'required|string|max:2000']);

        $feedback = Feddback::findOrFail($feedbackId);

        // Ensure office owns this feedback's service
        $officeUserId = Auth::id();
        $officeRequest = $feedback->serviceRequest()->with('service.office')->first();

        if ($officeRequest->service->office->user_id !== $officeUserId) {
            abort(403);
        }

        $feedback->update(['response' => $validated['response']]);

        return response()->json(['message' => 'Response submitted', 'data' => $feedback]);
    }

    // Office views all feedback for their services
    public function officeIndex()
    {
        $userId = Auth::id();

        $feedbacks = Feddback::whereHas('serviceRequest.service.office', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['serviceRequest.service', 'citizen'])->orderBy('created_at', 'desc')->get();

        return view('office.feedback.index', compact('feedbacks'));
    }
}
