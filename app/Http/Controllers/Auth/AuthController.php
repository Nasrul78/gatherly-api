<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $action)
    {
        $result = $action->execute($request->validated());

        return [
            'messages' => 'Successfully registered user.',
            'user' => UserResource::make($result->user),
            'token' => $result->token,
            'token_type' => 'Bearer'
        ];
    }
}
