<?php

namespace App\Core\Notifications\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Notifications — the user's notification feed page
 * (penova.notifications.index).
 *
 * Uses Laravel's native database notification channel (the standard
 * "notifications" table + Notifiable trait on the user), so any Core
 * module or product Module sends notifications the plain Laravel way:
 *
 *   $user->notify(new BookingConfirmed($booking));
 *
 * Core only owns the shared surface: this feed, the unread badge shared
 * via HandleInertiaRequests, and the bell component in the admin layout.
 */
class ListNotificationsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Core/Notifications/Index', [
            'notifications' => $request->user()
                ->notifications()
                ->paginate(config('penova.datatable.per_page')),
        ]);
    }
}
