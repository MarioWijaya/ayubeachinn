<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekLevel
{
    public function handle($request, Closure $next, ...$levels)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        // owner full akses
        if ($user->level === 'owner') {
            return $next($request);
        }

        if (!in_array($user->level, $levels, true)) {
            abort(403);
        }

        return $next($request);
    }
}
