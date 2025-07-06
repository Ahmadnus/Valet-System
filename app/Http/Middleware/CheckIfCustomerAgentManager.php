<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfCustomerAgentManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->userable_type) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $allowedTypes = ['Customer', 'Agent', 'Manager'];
        $currentType = class_basename($user->userable_type);

        if (in_array($currentType, $allowedTypes)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden: access denied.'], 403);
    }
}
