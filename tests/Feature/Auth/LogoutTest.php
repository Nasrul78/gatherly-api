<?php

use App\Models\User;

use function Pest\Laravel\postJson;

it('can logout with valid token and token is revoked', function () {
  $user = User::factory()->create();
  $token = $user->createToken('test-token')->plainTextToken;

  expect($user->tokens()->count())->toBe(1);

  postJson('/api/v1/auth/logout', [], [
    'Authorization' => 'Bearer ' . $token
  ])->assertOk()
    ->assertJson([
      'message' => 'Successfully logged out.'
    ]);

  $user->refresh();
  expect($user->tokens()->count())->toBe(0);
});

it('cannot logout without authentication', function () {
  postJson('/api/v1/auth/logout')
    ->assertUnauthorized();
});

it('cannot logout with invalid token', function () {
  postJson('/api/v1/auth/logout', [], [
    'Authorization' => 'Bearer invalid-token'
  ])->assertUnauthorized();
});

it('cannot logout with malformed authorization header', function () {
  postJson('/api/v1/auth/logout', [], [
    'Authorization' => 'InvalidFormat token'
  ])->assertUnauthorized();
});

it('can logout multiple users from the same account', function () {
  $user = User::factory()->create();
  $token1 = $user->createToken('test-token')->plainTextToken;
  $token2 = $user->createToken('test-token')->plainTextToken;

  expect($user->tokens()->count())->toBe(2);

  postJson('/api/v1/auth/logout', [], [
    'Authorization' => 'Bearer ' . $token1
  ])->assertOk();

  $user->refresh();
  expect($user->tokens()->count())->toBe(1);

  postJson('/api/v1/auth/logout', [], [
    'Authorization' => 'Bearer ' . $token2
  ])->assertOk();

  $user->refresh();
  expect($user->tokens()->count())->toBe(0);
});
