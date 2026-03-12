<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!\Auth::guard('admin')->check()) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
