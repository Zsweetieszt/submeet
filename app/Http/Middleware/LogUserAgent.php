<?php

namespace App\Http\Middleware;

use App\Models\UserLogs;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class LogUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
    
        $user = request()->user();
        $userId = $user ? $user->user_id : null;

        if ($userId === null) {
            $response = $next($request);
            $user = request()->user();
            if ($user) {
                $userId = $user->user_id;
            } else {
                return $response;
            }
        }else{
            $response = $next($request);
        }

        UserLogs::create([
            'user_id' => $userId,
            'ip_address' => $request->getClientIp(),
            'user_log_type' => $request->is('login') ? 'Login' : 'Logout',
            'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);

        return $response;
    }
}
