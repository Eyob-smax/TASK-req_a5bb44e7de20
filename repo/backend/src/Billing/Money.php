<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

use InvalidArgumentException;
use OverflowException;

final class Money
{
    private const INT_MAX_SAFE = PHP_INT_MAX;
    private const INT_MIN_SAFE = PHP_INT_MIN;

    private function __construct(
        public readonly int $cents,
    ) {
    }

    public static function ofCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        $sum = $this->cents + $other->cents;
        if (($other->cents > 0 && $sum < $this->cents) || ($other->cents < 0 && $sum > $this->cents)) {
            throw new OverflowException('Money addition overflow.');
        }
        return new self($sum);
    }

    public function subtract(self $other): self
    {
        $diff = $this->cents - $other->cents;
        if (($other->cents > 0 && $diff > $this->cents) || ($other->cents < 0 && $diff < $this->cents)) {
            throw new OverflowException('Money subtraction overflow.');
        }
        return new self($diff);
    }

    public function multiplyBps(int $bps): self
    {
        if ($bps < 0) {
            throw new InvalidArgumentException('Basis points cannot be negative.');
        }
        $product = $this->cents * $bps;
        if ($this->cents !== 0 && intdiv($product, $this->cents) !== $bps) {
            throw new OverflowException('Money multiplication overflow.');
        }
        return new self(self::roundHalfUpDiv($product, 10000));
    }

    public function multiplyInt(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Multiplier cannot be negative.');
        }
        $product = $this->cents * $factor;
        if ($this->cents !== 0 && intdiv($product, $this->cents) !== $factor) {
            throw new OverflowException('Money multiplication overflow.');
        }
        return new self($product);
    }

    /**
     * Allocate this amount into $parts using integer weights.
     * Remainder cents are distributed to the earliest buckets one cent at a time.
     *
     * @param int[] $weights
     * @return self[]
     */
    public function allocate(array $weights): array
    {
        if ($weights === []) {
            throw new InvalidArgumentException('Allocation weights must not be empty.');
        }
        $total = 0;
        foreach ($weights as $weight) {
            if ($weight < 0) {
                throw new InvalidArgumentException('Allocation weights must be non-negative.');
            }
            $total += $weight;
        }
        if ($total === 0) {
            throw new InvalidArgumentException('Allocation weights sum must be positive.');
        }

        $result = [];
        $remaining = $this->cents;
        foreach ($weights as $i => $weight) {
            $share = intdiv($this->cents * $weight, $total);
            $result[$i] = $share;
            $remaining -= $share;
        }
        $i = 0;
        $count = count($weights);
        while ($remaining > 0) {
            $result[$i % $count]++;
            $remaining--;
            $i++;
        }
        return array_map(static fn (int $c) => new self($c), $result);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function format(): string
    {
        $sign = $this->cents < 0 ? '-' : '';
        $abs = abs($this->cents);
        $dollars = intdiv($abs, 100);
        $cents = $abs % 100;
        return sprintf('%s%d.%02d', $sign, $dollars, $cents);
    }

    public static function roundHalfUpDiv(int $numerator, int $denominator): int
    {
        if ($denominator === 0) {
            throw new InvalidArgumentException('Denominator must not be zero.');
        }
        if ($denominator < 0) {
            $numerator = -$numerator;
            $denominator = -$denominator;
        }
        if ($numerator >= 0) {
            return intdiv($numerator + intdiv($denominator, 2), $denominator);
        }
        return -intdiv(-$numerator + intdiv($denominator, 2), $denominator);
    }
}
