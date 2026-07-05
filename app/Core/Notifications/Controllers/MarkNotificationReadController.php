<?php

namespace App\Core\Notifications\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Core\Notifications — marks one notification (or all, with id "all")
 * as read (penova.notifications.read).
 */
class MarkNotificationReadController extends Controller
{
    public function __invoke(Request $request, string $id): RedirectResponse
    {
        if ($id === 'all') {
            $request->user()->unreadNotifications->markAsRead();
        } else {
            $request->user()->notifications()->findOrFail($id)->markAsRead();
        }

        return back();
    }
}
