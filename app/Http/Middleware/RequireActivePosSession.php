<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActivePosSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Only apply to cashiers
        if (!$user || !$user->isCashier()) {
            return $next($request);
        }
        
        // Check if user has an active POS session
        $currentSession = $user->currentPosSession();
        
        if (!$currentSession) {
            // If accessing POS terminal without active session, redirect to start session page
            if ($request->routeIs('pos.index') || $request->routeIs('pos.sale')) {
                return redirect()->route('pos.start-session')
                    ->with('warning', 'You need to start a POS session before accessing the terminal.');
            }
        }
        
        return $next($request);
    }
}
