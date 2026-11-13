<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            abort(401, 'Unauthorized: Anda belum login ke sistem.');
        }

        if (! Auth::user()->isAdmin()) {
            abort(403, 'Access Denied: Anda tidak memiliki hak akses Administrator.');
        }

        if (! Auth::user()->isActive()) {
            Auth::logout();
            abort(403, 'Access Denied: Akun Anda telah dinonaktifkan oleh Administrator.');
        }

        return $next($request);
    }
}
