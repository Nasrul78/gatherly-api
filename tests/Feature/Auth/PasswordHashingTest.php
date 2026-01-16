<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

it('hashes password when registering new user', function () {
  $userData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
  ];

  postJson('/api/v1/auth/register', $userData)
    ->assertCreated();

  $user = User::where('email', 'test@example.com')->first();

  expect($user)->not->toBeNull();
  expect($user->password)->not->toBe('password123');
  expect(Hash::check('password123', $user->password))->toBeTrue();
});

it('does not store plain text password in database', function () {
  $plainPassword = 'plainpassword123';

  $userData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => $plainPassword,
    'password_confirmation' => $plainPassword,
  ];

  postJson('/api/v1/auth/register', $userData)
    ->assertCreated();

  $user = User::where('email', 'test@example.com')->first();

  expect($user->password)->not->toBe($plainPassword);
  expect($user->password)->not->toContain($plainPassword);
  expect(strlen($user->password))->toBe(60); // bcrypt hash length
});

it('uses bcrypt hashing algorithm', function () {
  $password = 'testpassword123';

  $userData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => $password,
    'password_confirmation' => $password,
  ];

  postJson('/api/v1/auth/register', $userData)
    ->assertCreated();

  $user = User::where('email', 'test@example.com')->first();

  // Check if it's a valid bcrypt hash (starts with $2y$)
  expect($user->password)->toStartWith('$2y$');
});

it('generates different hash for same password', function () {
  $password = 'samepassword123';

  // Create two users with same password
  $userData1 = [
    'name' => 'User One',
    'email' => 'user1@example.com',
    'password' => $password,
    'password_confirmation' => $password,
  ];

  $userData2 = [
    'name' => 'User Two',
    'email' => 'user2@example.com',
    'password' => $password,
    'password_confirmation' => $password,
  ];

  postJson('/api/v1/auth/register', $userData1)->assertCreated();
  postJson('/api/v1/auth/register', $userData2)->assertCreated();

  $user1 = User::where('email', 'user1@example.com')->first();
  $user2 = User::where('email', 'user2@example.com')->first();

  expect($user1->password)->not->toBe($user2->password);
  expect(Hash::check($password, $user1->password))->toBeTrue();
  expect(Hash::check($password, $user2->password))->toBeTrue();
});
