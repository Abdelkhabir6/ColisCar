<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Message;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id() && $booking->trip->driver_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $receiverId = $booking->user_id === Auth::id()
            ? $booking->trip->driver_id
            : $booking->user_id;

        $message = Message::create([
            'booking_id' => $booking->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'content' => $validated['content'],
        ]);

        // Notifier le destinataire du nouveau message
        $receiver = \App\Models\User::find($receiverId);
        if ($receiver) {
            $receiver->notify(new NewMessage($message));
        }

        return back()->with('success', 'Message envoyé !');
    }

    public function markAsRead(Message $message)
    {
        if ($message->receiver_id !== Auth::id()) {
            abort(403);
        }

        $message->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}

