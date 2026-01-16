<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json([
            'messages' => 'Successfully registered user.',
            'user' => UserResource::make($result->user),
            'token' => $result->token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(LoginRequest $request, LoginUser $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json([
            'messages' => 'Successfully login user.',
            'user' => UserResource::make($result->user),
            'token' => $result->token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(#[CurrentUser] $user): JsonResponse
    {
        // Always get the token from the request to avoid caching issues
        $request = request();
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'No token provided.'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken || $accessToken->tokenable_id !== $user->id) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        $accessToken->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }
}
