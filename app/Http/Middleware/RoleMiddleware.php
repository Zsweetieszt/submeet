<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();
        $eventCode = $request->route('event');
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        $hasRole = $user->user_events()
            ->whereHas('role', function ($q) use ($role) {
                $q->where('role_name', $role);
            })
            ->whereHas('event', function ($q) use ($eventCode) {
                $q->where('event_code', $eventCode);
            })
            ->exists();
        if ($user->root || $hasRole) {
            return $next($request);
        }
        abort(403, 'Unauthorized');
    }
}
