<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (is_null(Auth::user()->activated_at) || Auth::user()->status == false) {
            return redirect('/email/verify')->with('message', 'Please verify your email.');
        }

        return $next($request);
    }
}
