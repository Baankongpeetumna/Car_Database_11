<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles,
    ): Response {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && in_array($user->role, $roles, true),
            403,
            'You do not have permission to access this page.',
        );

        return $next($request);
    }
}