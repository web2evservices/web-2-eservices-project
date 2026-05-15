<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageReceived;
use App\Models\Messages;
use App\Models\ServiceRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessagesController extends Controller
{
    /**
     * Get messages for a service request or user conversation
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $serviceRequestId = $request->query('service_request_id');

        $query = Messages::with(['sender', 'receiver', 'serviceRequest'])
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            });

        if ($serviceRequestId) {
            $query->where('service_request_id', $serviceRequestId);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json(['data' => $messages]);
    }

    /**
     * Get specific conversation
     */
    public function show($id)
    {
        $message = Messages::with(['sender', 'receiver', 'serviceRequest'])
            ->findOrFail($id);

        // Verify access
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json(['data' => $message]);
    }

    /**
     * Store a new message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'service_request_id' => 'nullable|integer|exists:service_requests,id',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $attachment = $file->storeAs('messages', $fileName, 'public');
        }

        $message = Messages::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'service_request_id' => $validated['service_request_id'] ?? null,
            'message' => $validated['message'],
            'attachment' => $attachment,
        ]);

        // Dispatch event to notify receiver (if office user)
        ChatMessageReceived::dispatch($message);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }

    /**
     * Download message attachment
     */
    public function downloadAttachment($messageId)
    {
        $message = Messages::findOrFail($messageId);

        // Verify access
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$message->attachment) {
            abort(404, 'No attachment found');
        }

        $filePath = storage_path('app/public/' . $message->attachment);
        return response()->download($filePath);
    }

    /**
     * Delete a message
     */
    public function destroy($id)
    {
        $message = Messages::findOrFail($id);

        // Only sender can delete
        if ($message->sender_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }
}
