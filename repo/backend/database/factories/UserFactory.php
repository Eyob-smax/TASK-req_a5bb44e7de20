<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'                => fake()->name(),
            'email'               => fake()->unique()->safeEmail(),
            'password'            => Hash::make('password1234'),
            'locale'              => 'en',
            'status'              => AccountStatus::Active,
            'last_login_at'       => null,
            'password_changed_at' => null,
        ];
    }

    public function locked(): static
    {
        return $this->state(['status' => AccountStatus::Locked]);
    }

    public function disabled(): static
    {
        return $this->state(['status' => AccountStatus::Disabled]);
    }

    public function asAdmin(): static
    {
        return $this->afterCreating(function (User $user): void {
            UserRole::create([
                'user_id'    => $user->id,
                'role'       => RoleName::Administrator,
                'scope_type' => 'global',
                'scope_id'   => null,
            ]);
        });
    }

    public function asStudent(): static
    {
        return $this->afterCreating(function (User $user): void {
            UserRole::create([
                'user_id'    => $user->id,
                'role'       => RoleName::Student,
                'scope_type' => 'global',
                'scope_id'   => null,
            ]);
        });
    }

    public function asTeacher(): static
    {
        return $this->afterCreating(function (User $user): void {
            UserRole::create([
                'user_id'    => $user->id,
                'role'       => RoleName::Teacher,
                'scope_type' => 'global',
                'scope_id'   => null,
            ]);
        });
    }

    public function asRegistrar(): static
    {
        return $this->afterCreating(function (User $user): void {
            UserRole::create([
                'user_id'    => $user->id,
                'role'       => RoleName::Registrar,
                'scope_type' => 'global',
                'scope_id'   => null,
            ]);
        });
    }
}
