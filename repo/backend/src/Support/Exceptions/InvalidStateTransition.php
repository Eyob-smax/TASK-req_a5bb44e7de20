<?php

declare(strict_types=1);

namespace CampusLearn\Support\Exceptions;

use DomainException;

final class InvalidStateTransition extends DomainException
{
    public function __construct(
        string $domain,
        string $from,
        string $event,
    ) {
        parent::__construct(sprintf(
            'Invalid %s transition: "%s" on state "%s".',
            $domain,
            $event,
            $from,
        ));
    }
}
