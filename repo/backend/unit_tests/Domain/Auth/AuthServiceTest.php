<?php

declare(strict_types=1);

namespace Tests\Domain\Auth;

use App\Enums\AccountStatus;
use App\Models\AccountLock;
use App\Models\FailedLoginAttempt;
use App\Models\User;
use App\Services\AuthService;
use CampusLearn\Auth\LoginThrottlePolicy;
use CampusLearn\Auth\PasswordRule;
use CampusLearn\Support\Exceptions\AccountLocked;
use CampusLearn\Support\Exceptions\InvalidCredentials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeAuthService(int $threshold = 5, int $window = 15, int $lock = 15): AuthService
{
    return new AuthService(
        passwordRule:    new PasswordRule(10),
        throttlePolicy:  new LoginThrottlePolicy($threshold, $window, $lock),
        tokenTtlMinutes: 720,
    );
}

test('successful login returns token and user payload', function () {
    $user = User::factory()->create([
        'email'    => 'alice@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    $result = makeAuthService()->login('alice@example.com', 'correctpassword', '127.0.0.1');

    expect($result)->toHaveKeys(['token', 'expires_at', 'user'])
        ->and($result['user']['email'])->toBe('alice@example.com');
});

test('wrong password throws InvalidCredentials', function () {
    User::factory()->create([
        'email'    => 'bob@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    expect(fn () => makeAuthService()->login('bob@example.com', 'wrongpassword', '127.0.0.1'))
        ->toThrow(InvalidCredentials::class);
});

test('unknown email throws InvalidCredentials', function () {
    expect(fn () => makeAuthService()->login('nobody@example.com', 'any', '127.0.0.1'))
        ->toThrow(InvalidCredentials::class);
});

test('5 failed attempts locks the account', function () {
    $user = User::factory()->create([
        'email'    => 'charlie@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    $service = makeAuthService(threshold: 5);

    for ($i = 0; $i < 4; $i++) {
        try {
            $service->login('charlie@example.com', 'wrong', '127.0.0.1');
        } catch (InvalidCredentials) {
        }
    }

    // Fifth attempt should lock
    expect(fn () => $service->login('charlie@example.com', 'wrong', '127.0.0.1'))
        ->toThrow(AccountLocked::class);

    expect(AccountLock::where('user_id', $user->id)->exists())->toBeTrue();
});

test('locked account rejects correct password until unlocked', function () {
    $user = User::factory()->create([
        'email'    => 'dave@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    AccountLock::create([
        'user_id'   => $user->id,
        'locked_at' => now()->subMinute(),
        'unlock_at' => now()->addMinutes(14),
        'reason'    => 'test',
    ]);

    expect(fn () => makeAuthService()->login('dave@example.com', 'correctpassword', '127.0.0.1'))
        ->toThrow(AccountLocked::class);
});

test('failed attempts outside the window do not contribute to lock count', function () {
    $user = User::factory()->create([
        'email'    => 'eve@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    // Insert 4 old attempts outside window
    for ($i = 0; $i < 4; $i++) {
        FailedLoginAttempt::create([
            'email'        => 'eve@example.com',
            'ip'           => '127.0.0.1',
            'attempted_at' => now()->subMinutes(20),
        ]);
    }

    // One in-window failure should NOT lock (threshold=5, only 1 in window)
    expect(fn () => makeAuthService()->login('eve@example.com', 'wrong', '127.0.0.1'))
        ->toThrow(InvalidCredentials::class);

    expect(AccountLock::where('user_id', $user->id)->exists())->toBeFalse();
});

test('successful login clears failed attempts', function () {
    $user = User::factory()->create([
        'email'    => 'frank@example.com',
        'password' => Hash::make('correctpassword'),
        'status'   => AccountStatus::Active,
    ]);

    FailedLoginAttempt::create([
        'email'        => 'frank@example.com',
        'ip'           => '127.0.0.1',
        'attempted_at' => now()->subMinute(),
    ]);

    makeAuthService()->login('frank@example.com', 'correctpassword', '127.0.0.1');

    expect(FailedLoginAttempt::where('email', 'frank@example.com')->count())->toBe(0);
});
