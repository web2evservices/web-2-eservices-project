<?php

namespace App\Http\Controllers;

use App\Events\FeedbackReceived;
use App\Models\ServiceRequests;
use App\Models\Feddback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Store feedback for a service request
     */
    public function store(Request $request, $serviceRequestId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $serviceRequest = ServiceRequests::findOrFail($serviceRequestId);

        // Ensure the authenticated user is the citizen who made this request
        if ($serviceRequest->citizen_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback = Feddback::create([
            'service_request_id' => $serviceRequestId,
            'citizen_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'response' => null,
        ]);

        // Dispatch event to notify office
        FeedbackReceived::dispatch($feedback);

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'data' => $feedback,
        ], 201);
    }

    /**
     * Office responds to feedback
     */
    public function respondToFeedback(Request $request, $feedbackId)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $feedback = Feddback::findOrFail($feedbackId);
        $serviceRequest = $feedback->serviceRequest;
        $service = $serviceRequest->service;
        $office = $service->office;

        // Ensure the authenticated office user manages this office
        if ($office->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback->response = $validated['response'];
        $feedback->save();

        return response()->json([
            'message' => 'Response submitted successfully',
            'data' => $feedback,
        ], 200);
    }

    /**
     * Show feedback for a service request
     */
    public function show($serviceRequestId)
    {
        $feedback = Feddback::where('service_request_id', $serviceRequestId)
            ->with('citizen')
            ->first();

        if (!$feedback) {
            return response()->json(['message' => 'Feedback not found'], 404);
        }

        return response()->json(['data' => $feedback]);
    }
}
