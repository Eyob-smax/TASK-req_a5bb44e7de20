<?php

declare(strict_types=1);

namespace CampusLearn\Support\Exceptions;

use RuntimeException;

final class AccountLocked extends RuntimeException
{
    public function __construct(string $message = 'Account is temporarily locked due to too many failed login attempts.')
    {
        parent::__construct($message);
    }
}
