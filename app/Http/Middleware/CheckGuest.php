<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckGuest
{
  public function handle(Request $request, Closure $next): Response
  {
    $token = $request->bearerToken();

    if ($token && PersonalAccessToken::findToken($token)) {
      return response()->json(['message' => 'Already authenticated.'], Response::HTTP_FORBIDDEN);
    }

    return $next($request);
  }
}
