<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = $request->user();
        
        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Check if this is an API request
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Insufficient permissions.'
            ], 403);
        }

        // For web requests, redirect to dashboard with error message
        return redirect()->route('dashboard')
            ->with('error', 'Access denied. You do not have permission to access this page.');
    }
}