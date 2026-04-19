<?php

declare(strict_types=1);

namespace CampusLearn\Support\Exceptions;

use DomainException;

final class EditWindowExpired extends DomainException
{
    public function __construct(int $windowMinutes)
    {
        parent::__construct(sprintf('Edit window of %d minutes has expired.', $windowMinutes));
    }
}
