<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);

        $notification->update(['lu_at' => now()]);

        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function markAll(Request $request)
    {
        $request->user()->notifications()->whereNull('lu_at')->update(['lu_at' => now()]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
