<?php

declare(strict_types=1);

namespace CampusLearn\Auth;

final class PasswordRule
{
    public function __construct(
        private readonly int $minLength = 10,
    ) {
    }

    /**
     * @return string[] list of violation codes; empty when the password passes.
     */
    public function validate(string $password): array
    {
        $violations = [];
        if ($password === '') {
            $violations[] = 'PASSWORD_EMPTY';
            return $violations;
        }
        if (mb_strlen($password) < $this->minLength) {
            $violations[] = 'PASSWORD_TOO_SHORT';
        }
        return $violations;
    }

    public function minLength(): int
    {
        return $this->minLength;
    }
}
