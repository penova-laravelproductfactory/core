<?php

namespace App\Core\Roles\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Core\Roles — route middleware, registered as "permission" by
 * PenovaCoreServiceProvider.
 *
 * Usage (Core and Modules alike):
 *   Route::get(...)->middleware('permission:users.manage');
 */
class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
