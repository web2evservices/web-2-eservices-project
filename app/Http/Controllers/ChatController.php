<?php
namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Messages;
use App\Models\User;
use App\Models\Notifications;
use App\Models\Government_Offices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    // Citizen: list all conversations (distinct office users they've messaged)
    public function index()
    {
        $userId = Auth::id();

        $conversations = Messages::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            })
            ->map(fn($msgs) => $msgs->first());

        return view('users.chat.index', compact('conversations'));
    }

    // Show chat with a specific user
    public function show($otherUserId)
    {
        $userId = Auth::id();
        $otherUser = User::findOrFail($otherUserId);

        $messages = Messages::where(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $userId)->where('receiver_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $otherUserId)->where('receiver_id', $userId);
        })->orderBy('created_at')->get();

        return view('users.chat.show', compact('messages', 'otherUser'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'receiver_id'        => 'required|integer|exists:users,id',
            'message'            => 'required|string|max:2000',
            'service_request_id' => 'nullable|integer|exists:service_requests,id',
            'attachment'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat-attachments', 'public');
        }

        $msg = Messages::create([
            'sender_id'          => Auth::id(),
            'receiver_id'        => $validated['receiver_id'],
            'service_request_id' => $validated['service_request_id'] ?? null,
            'message'            => $validated['message'],
            'attachment'         => $attachmentPath,
        ]);

        event(new NewMessageEvent($msg));

        // Notify receiver
        Notifications::create([
            'user_id' => $validated['receiver_id'],
            'title'   => 'New Message',
            'message' => Auth::user()->username . ' sent you a message.',
            'type'    => 'chat',
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Sent', 'data' => $msg->load('sender')]);
    }

    // Office-side: list all citizen conversations
    public function officeIndex()
    {
        $userId = Auth::id();

        $conversations = Messages::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            })
            ->map(fn($msgs) => $msgs->first());

        return view('office.chat.index', compact('conversations'));
    }

    public function officeShow($otherUserId)
    {
        $userId = Auth::id();
        $otherUser = User::findOrFail($otherUserId);

        $messages = Messages::where(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $userId)->where('receiver_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $otherUserId)->where('receiver_id', $userId);
        })->orderBy('created_at')->get();

        return view('office.chat.show', compact('messages', 'otherUser'));
    }
}