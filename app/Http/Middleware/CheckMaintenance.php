<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!AppSetting::maintenanceMode()) {
            return $next($request);
        }

        // Admin panel is always accessible during maintenance
        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        // Bypass session flag: only allows the POST login form submission.
        // The GET login page is served directly by the secret bypass route — never via /auth/login.
        if (session('maintenance_bypass')
            && $request->isMethod('POST')
            && $request->is('auth/login')) {
            return $next($request);
        }

        $message = AppSetting::get('maintenance_message', 'Sistem sedang dalam pemeliharaan. Mohon tunggu sebentar.');
        return response()->view('maintenance', ['message' => $message], 503);
    }
}
