<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            abort(401, 'Unauthorized: Anda belum login ke sistem.');
        }

        if (Auth::user()->isAdmin()) {
            abort(403, 'Access Denied: Administrator tidak dapat mengakses halaman khusus pelanggan.');
        }

        if (! Auth::user()->isActive()) {
            Auth::logout();
            abort(403, 'Access Denied: Akun Anda telah dinonaktifkan oleh Administrator.');
        }

        return $next($request);
    }
}
