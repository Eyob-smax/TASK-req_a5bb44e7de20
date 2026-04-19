<?php

declare(strict_types=1);

namespace Tests\Domain\Auth;

use CampusLearn\Auth\PasswordRule;
use PHPUnit\Framework\TestCase;

final class PasswordRuleTest extends TestCase
{
    public function testEmptyRejected(): void
    {
        $rule = new PasswordRule(10);
        $this->assertSame(['PASSWORD_EMPTY'], $rule->validate(''));
    }

    public function testTooShortRejected(): void
    {
        $rule = new PasswordRule(10);
        $this->assertSame(['PASSWORD_TOO_SHORT'], $rule->validate('short'));
    }

    public function testExactlyMinLengthAccepted(): void
    {
        $rule = new PasswordRule(10);
        $this->assertSame([], $rule->validate('1234567890'));
    }

    public function testLongPasswordAccepted(): void
    {
        $rule = new PasswordRule(10);
        $this->assertSame([], $rule->validate('this-password-is-long'));
    }
}
