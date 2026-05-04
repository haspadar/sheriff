<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Files;

use Haspadar\Sheriff\File\File;
use PHPUnit\Framework\Constraint\Constraint;

final class HasFileContents extends Constraint
{
    public function __construct(
        private readonly string $expected,
    ) {}

    protected function matches($other): bool
    {
        return $other instanceof File
            && $other->contents() === $this->expected;
    }

    public function toString(): string
    {
        return 'has contents ' . var_export($this->expected, true);
    }

    protected function additionalFailureDescription($other): string
    {
        if (!$other instanceof File) {
            return "\nBut object of type "
                . get_debug_type($other)
                . ' was given instead of File';
        }

        return "\nExpected: " . var_export($this->expected, true)
            . "\nBut was:  " . var_export($other->contents(), true);
    }
}
