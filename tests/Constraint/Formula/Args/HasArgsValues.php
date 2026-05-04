<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Formula\Args;

use Haspadar\Sheriff\Formula\Args\Args;
use PHPUnit\Framework\Constraint\Constraint;

final class HasArgsValues extends Constraint
{
    /**
     * @param list<int|float|string|bool> $expected
     */
    public function __construct(
        private readonly array $expected,
    ) {}

    public function toString(): string
    {
        return 'has args values ' . $this->export($this->expected);
    }

    protected function matches($other): bool
    {
        return $other instanceof Args
            && $other->values() === $this->expected;
    }

    protected function additionalFailureDescription($other): string
    {
        if (!$other instanceof Args) {
            return "\nBut object of type "
                . get_debug_type($other)
                . ' was given instead of Args';
        }

        return "\nExpected: {$this->export($this->expected)}"
            . "\nBut was:  {$this->export($other->values())}";
    }

    private function export(mixed $value): string
    {
        return var_export($value, true);
    }
}
