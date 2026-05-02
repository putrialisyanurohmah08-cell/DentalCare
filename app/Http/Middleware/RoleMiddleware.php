<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $roleValues = collect($roles)->map(fn (string $role) => UserRole::from($role)->value);

        if (! $roleValues->contains($user->role->value)) {
            abort(403);
        }

        return $next($request);
    }
}
