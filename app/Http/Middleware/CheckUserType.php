<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    public function handle($request, Closure $next, $type)
    {
        $user = Auth::user();

        if (!$user || !$user->userable_type) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $currentType = class_basename($user->userable_type);

        // اسمح للـ Manager يدخل كل مكان
        if ($currentType === 'Manager') {
            return $next($request);
        }

        if (strtolower($currentType) === strtolower($type)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}