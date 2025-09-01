<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // admin ガードで認証済みか確認
        if (!Auth::guard('admin')->check()) {
            return response()->json(['message' => '管理者以外は許可されていません'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
