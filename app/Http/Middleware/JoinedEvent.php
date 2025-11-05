<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JoinedEvent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route('event') ?? $request->event;
        $event = Event::where('event_code', $event)->first();
        $event = $event->event_id;
        $user_events = Auth::user()->user_events->pluck('event_id')->toArray();

        if (!in_array($event, $user_events) && !Auth::user()->root) {
            return redirect()->route("events");
        }

        return $next($request);
    }
}
