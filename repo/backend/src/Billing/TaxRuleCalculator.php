<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

use InvalidArgumentException;

final class TaxRuleCalculator
{
    public function computeTax(Money $taxableBase, int $rateBps): Money
    {
        if ($rateBps < 0) {
            throw new InvalidArgumentException('Tax rate bps cannot be negative.');
        }
        if ($taxableBase->isNegative()) {
            throw new InvalidArgumentException('Taxable base cannot be negative.');
        }
        return $taxableBase->multiplyBps($rateBps);
    }
}
