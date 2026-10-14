<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Mark a single notification read and redirect to the order it's about.
     */
    public function show(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->route('admin.order.show', $notification->data['order_id']);
    }

    /**
     * Mark every unread notification read, then return to the previous page.
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }
}
