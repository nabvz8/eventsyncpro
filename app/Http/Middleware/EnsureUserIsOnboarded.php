<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOnboarded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $hasWorkspace = $user->workspaces()->exists() || $user->joinedWorkspaces()->exists();

            if (!$hasWorkspace && !$request->is('onboarding*')) {
                return redirect('/onboarding');
            }

            if ($hasWorkspace && $request->is('onboarding*')) {
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
