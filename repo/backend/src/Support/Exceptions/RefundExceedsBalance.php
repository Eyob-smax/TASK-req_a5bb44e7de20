<?php

declare(strict_types=1);

namespace CampusLearn\Support\Exceptions;

use DomainException;

final class RefundExceedsBalance extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
