<?php

use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

it('can register a new user', function () {
  $userData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
  ];

  postJson('/api/v1/auth/register', $userData)
    ->assertOk()
    ->assertJsonStructure([
      'messages',
      'user' => [
        'id',
        'name',
        'email',
        'email_verified',
      ],
      'token',
      'token_type',
    ])
    ->assertJson([
      'messages' => 'Successfully registered user.',
      'token_type' => 'Bearer',
    ]);

  assertDatabaseHas('users', [
    'name' => 'Test User',
    'email' => 'test@example.com',
  ]);

  $user = User::where('email', 'test@example.com')->first();
  assertNotNull($user);
  assertEquals('Test User', $user->name);
});

it('requires name for registration', function () {
  postJson('/api/v1/auth/register', [
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
  ])->assertUnprocessable();
});

it('requires email for registration', function () {
  postJson('/api/v1/auth/register', [
    'name' => 'Test User',
    'password' => 'password123',
    'password_confirmation' => 'password123',
  ])->assertUnprocessable();
});

it('requires password for registration', function () {
  postJson('/api/v1/auth/register', [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password_confirmation' => 'password123',
  ])->assertUnprocessable();
});

it('requires password confirmation for registration', function () {
  postJson('/api/v1/auth/register', [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
  ])->assertUnprocessable();
});

it('requires valid email format for registration', function () {
  postJson('/api/v1/auth/register', [
    'name' => 'Test User',
    'email' => 'invalid-email',
    'password' => 'password123',
    'password_confirmation' => 'password123',
  ])->assertUnprocessable();
});

it('requires minimum name length for registration', function () {
  postJson('/api/v1/auth/register', [
    'name' => 'ab',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
  ])->assertUnprocessable();
});

it('requires minimum password length for registration', function () {
  postJson('/api/v1/auth/register', [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => '123',
    'password_confirmation' => '123',
  ])->assertUnprocessable();
});

it('requires matching password confirmation for registration', function () {
  postJson('/api/v1/auth/register', [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'different',
  ])->assertUnprocessable();
});

it('prevents duplicate email registration', function () {
  User::factory()->create([
    'email' => 'existing@example.com',
  ]);

  $userData = [
    'name' => 'New User',
    'email' => 'existing@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
  ];

  postJson('/api/v1/auth/register', $userData)
    ->assertUnprocessable()
    ->assertJsonValidationErrors(['email']);

  assertDatabaseCount('users', 1);
});
