<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\ServiceRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $serviceRequest = ServiceRequests::where('id', $id)
            ->where('citizen_id', Auth::id())
            ->firstOrFail();

        $path = $request->file('file')->store('documents', 'public');

        $document = Documents::create([
            'service_request_id' => $serviceRequest->id,
            'document_type' => 'uploaded',
            'file_path' => $path
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }
}
