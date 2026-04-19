<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\AccountLock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('successful login returns token and user envelope', function () {
    User::factory()->create([
        'email'    => 'login@example.com',
        'password' => Hash::make('goodpassword1'),
        'status'   => AccountStatus::Active,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'login@example.com',
        'password' => 'goodpassword1',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['token', 'expires_at', 'user' => ['id', 'email', 'roles']]])
        ->assertJsonPath('data.user.email', 'login@example.com');
});

test('wrong password returns 401 INVALID_CREDENTIALS', function () {
    User::factory()->create([
        'email'    => 'wrong@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'wrong@example.com',
        'password' => 'badpassword',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

test('unknown email returns 401 INVALID_CREDENTIALS', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'nobody@example.com',
        'password' => 'anypassword',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

test('locked account returns 423 ACCOUNT_LOCKED', function () {
    $user = User::factory()->create([
        'email'    => 'locked@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    AccountLock::create([
        'user_id'   => $user->id,
        'locked_at' => now()->subMinute(),
        'unlock_at' => now()->addMinutes(14),
        'reason'    => 'test lock',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'locked@example.com',
        'password' => 'correctpassword',
    ]);

    $response->assertStatus(423)
        ->assertJsonPath('error.code', 'ACCOUNT_LOCKED');
});

test('missing email field returns 422 validation error', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'somepassword',
    ]);

    $response->assertStatus(422);
});

test('login response includes X-Correlation-Id header', function () {
    User::factory()->create([
        'email'    => 'corr@example.com',
        'password' => Hash::make('goodpassword1'),
        'status'   => AccountStatus::Active,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'corr@example.com',
        'password' => 'goodpassword1',
    ]);

    $response->assertHeader('X-Correlation-Id');
});
