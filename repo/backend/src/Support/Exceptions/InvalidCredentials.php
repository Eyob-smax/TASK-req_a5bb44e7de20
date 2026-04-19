<?php

declare(strict_types=1);

namespace CampusLearn\Support\Exceptions;

use RuntimeException;

final class InvalidCredentials extends RuntimeException
{
    public function __construct(string $message = 'These credentials do not match our records.')
    {
        parent::__construct($message);
    }
}
