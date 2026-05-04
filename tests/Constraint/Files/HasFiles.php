<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Files;

use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\Files\Files;
use PHPUnit\Framework\Constraint\Constraint;

final class HasFiles extends Constraint
{
    /**
     * @param array<string, string> $expected path => contents
     */
    public function __construct(
        private readonly array $expected,
    ) {}

    public function toString(): string
    {
        return 'has files ' . $this->export($this->expected);
    }

    protected function matches($other): bool
    {
        if (!$other instanceof Files) {
            return false;
        }

        $actual = [];

        foreach ($other->all() as $file) {
            if (!$file instanceof File) {
                return false;
            }

            $actual[$file->name()] = $file->contents();
        }

        ksort($actual);
        $expected = $this->expected;
        ksort($expected);

        return $actual === $expected;
    }

    protected function failureDescription($other): string
    {
        return 'files ' . $this->toString();
    }

    protected function additionalFailureDescription($other): string
    {
        if (!$other instanceof Files) {
            return "\nBut object of type "
                . get_debug_type($other)
                . ' was given instead of Files';
        }

        $actual = [];
        foreach ($other->all() as $file) {
            $actual[$file->name()] = $file->contents();
        }
        ksort($actual);

        return "\nExpected: {$this->export($this->expected)}"
            . "\nBut was:  {$this->export($actual)}";
    }

    private function export(mixed $value): string
    {
        return var_export($value, true);
    }
}
