<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Storage;

use Haspadar\Sheriff\Storage\Storage;
use PHPUnit\Framework\Constraint\Constraint;

final class HasEntries extends Constraint
{
    /**
     * @param list<string> $expected
     */
    public function __construct(
        private readonly string $location,
        private readonly array $expected,
    ) {}

    public function toString(): string
    {
        return "has entries {$this->export($this->expected)} under {$this->export($this->location)}";
    }

    #[\Override]
    protected function matches(mixed $other): bool
    {
        if (!$other instanceof Storage) {
            return false;
        }

        $actual = iterator_to_array(
            $other->entries($this->location),
        );

        sort($actual);
        $expected = $this->expected;
        sort($expected);

        return $actual === $expected;
    }

    #[\Override]
    protected function failureDescription(mixed $other): string
    {
        return 'storage ' . $this->toString();
    }

    #[\Override]
    protected function additionalFailureDescription(mixed $other): string
    {
        if (!$other instanceof Storage) {
            return "\nBut object of type "
                . get_debug_type($other)
                . ' was given instead of Storage';
        }

        $actual = iterator_to_array(
            $other->entries($this->location),
        );
        sort($actual);

        return "\nExpected: {$this->export($this->expected)}"
            . "\nBut was:  {$this->export($actual)}";
    }

    private function export(mixed $value): string
    {
        return var_export($value, true);
    }
}
