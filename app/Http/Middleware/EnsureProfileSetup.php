<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileSetup
{
    /**
     * If the authenticated BK/Siswa user still needs to complete first-time
     * setup (must_change_password = true) redirect them to the setup page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = \Auth::guard('bk')->user() ?? \Auth::guard('siswa')->user();

        if ($user && $user->must_change_password) {
            if (!$request->routeIs('user.setup', 'user.setup.store')) {
                return redirect()->route('user.setup');
            }
        }

        return $next($request);
    }
}
