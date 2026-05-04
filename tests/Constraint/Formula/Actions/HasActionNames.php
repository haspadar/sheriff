<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Formula\Actions;

use Haspadar\Sheriff\Formula\Action\Action;
use Haspadar\Sheriff\Formula\Actions\Actions;
use PHPUnit\Framework\Constraint\Constraint;

final class HasActionNames extends Constraint
{
    /**
     * @param list<string> $expected
     */
    public function __construct(
        private readonly array $expected,
    ) {}

    public function toString(): string
    {
        return 'has actions ' . var_export($this->expected, true);
    }

    protected function matches($other): bool
    {
        if (!$other instanceof Actions) {
            return false;
        }

        $names = array_map(
            fn(Action $a): string => $a::class,
            $other->all(),
        );

        return $names === $this->expected;
    }

    protected function additionalFailureDescription($other): string
    {
        if (!$other instanceof Actions) {
            return "\nBut object of type "
                . get_debug_type($other)
                . ' was given instead of Actions';
        }

        $actual = array_map(
            fn(Action $a): string => $a::class,
            $other->all(),
        );

        return "\nExpected: " . var_export($this->expected, true)
            . "\nBut was:  " . var_export($actual, true);
    }
}
