<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to authenticated BK / Guru accounts only.
 * Use on top of auth:bk,siswa where BK-only access is required.
 */
class EnsureIsBk
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Auth::guard('bk')->check()) {
            abort(403, 'Akses hanya untuk Guru BK.');
        }

        return $next($request);
    }
}
