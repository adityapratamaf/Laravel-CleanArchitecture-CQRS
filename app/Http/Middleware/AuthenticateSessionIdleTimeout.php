<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateSessionIdleTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $maxIdleSeconds = 30 * 60;
            $lastActivity = $request->session()->get('last_activity_at');

            if ($lastActivity && (time() - $lastActivity > $maxIdleSeconds)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('success', 'Session expired. Silakan login lagi.');
            }

            $request->session()->put('last_activity_at', time());
        }

        return $next($request);
    }
}