<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Formula;

use Haspadar\Sheriff\Formula\Formula;
use PHPUnit\Framework\Constraint\Constraint;

final class HasFormulaResult extends Constraint
{
    public function __construct(
        private readonly string $expected,
    ) {}

    public function toString(): string
    {
        return 'has formula result ' . var_export($this->expected, true);
    }

    protected function matches($other): bool
    {
        return $other instanceof Formula
            && $other->result() === $this->expected;
    }

    protected function additionalFailureDescription($other): string
    {
        if (!$other instanceof Formula) {
            return "\nBut object of type "
                . get_debug_type($other)
                . ' was given instead of Formula';
        }

        return "\nExpected: " . var_export($this->expected, true)
            . "\nBut was:  " . var_export($other->result(), true);
    }
}
