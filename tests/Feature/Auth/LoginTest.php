<?php

use App\Models\User;

use function Pest\Laravel\postJson;

it('can login with valid credentials', function () {
  $user = User::factory()->create([
    'password' => 'password123',
  ]);

  $loginData = [
    'email' => $user->email,
    'password' => 'password123',
  ];

  postJson('/api/v1/auth/login', $loginData)
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
      'messages' => 'Successfully login user.',
      'token_type' => 'Bearer',
    ]);
});

it('cannot login when authenticated', function () {
  $user = User::factory()->create([
    'password' => bcrypt('password')
  ]);
  $token = $user->createToken('test-token')->plainTextToken;

  postJson('/api/v1/auth/login', [
    'email' => $user->email,
    'password' => 'password',
  ], [
    'Authorization' => 'Bearer ' . $token
  ])->assertForbidden();
});

it('cannot login with wrong password', function () {
  $user = User::factory()->create([
    'password' => 'correctpassword',
  ]);

  postJson('/api/v1/auth/login', [
    'email' => $user->email,
    'password' => 'incorrectpassword',
  ])->assertUnprocessable()
    ->assertJsonValidationErrors(['email']);
});

it('cannot login with non-existent email', function () {
  postJson('/api/v1/auth/login', [
    'email' => 'nonexistent@example.com',
    'password' => 'password123',
  ])->assertUnprocessable()
    ->assertJsonValidationErrors(['email']);
});

it('requires email for login', function () {
  postJson('/api/v1/auth/login', [
    'password' => 'password123',
  ])->assertUnprocessable()
    ->assertJsonValidationErrors(['email']);
});

it('requires password for login', function () {
  postJson('/api/v1/auth/login', [
    'email' => 'test@example.com',
  ])->assertUnprocessable()
    ->assertJsonValidationErrors(['password']);
});

it('requires valid email format for login', function () {
  postJson('/api/v1/auth/login', [
    'email' => 'invalid-email',
    'password' => 'password123',
  ])->assertUnprocessable()
    ->assertJsonValidationErrors(['email']);
});

it('requires minimum password length for login', function () {
  postJson('/api/v1/auth/login', [
    'email' => 'test@example.com',
    'password' => '123',
  ])->assertUnprocessable()
    ->assertJsonValidationErrors(['password']);
});

it('creates new token on each login', function () {
  $user = User::factory()->create([
    'password' => 'password123',
  ]);

  $loginData = [
    'email' => $user->email,
    'password' => 'password123',
  ];

  $response1 = postJson('/api/v1/auth/login', $loginData);
  $token1 = $response1->json('token');

  $response2 = postJson('/api/v1/auth/login', $loginData);
  $token2 = $response2->json('token');

  expect($token1)->not->toBe($token2);
  expect($user->tokens()->count())->toBe(2);
});

it('is throttled after 10 login attempts per minute', function () {
  $user = User::factory()->create([
    'password' => 'password123',
  ]);

  $loginData = [
    'email' => $user->email,
    'password' => 'password123',
  ];

  // Make 10 successful requests (should all pass)
  for ($i = 0; $i < 10; $i++) {
    postJson('/api/v1/auth/login', $loginData)->assertOk();
  }

  // 11th request should be throttled
  postJson('/api/v1/auth/login', $loginData)
    ->assertStatus(429)
    ->assertJson([
      'message' => 'Too Many Attempts.'
    ]);
});
