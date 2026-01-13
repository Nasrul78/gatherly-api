<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUser
{
    public function execute(array $data): object
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return (object) [
                'user' => $user,
                'token' => $token,
            ];
        });
    }
}
