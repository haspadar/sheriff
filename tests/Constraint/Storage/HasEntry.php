<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Storage;

use Haspadar\Sheriff\Storage\Storage;
use PHPUnit\Framework\Constraint\Constraint;

final class HasEntry extends Constraint
{
    public function __construct(
        private readonly string $location,
        private readonly string $contents,
        private readonly int $mode = 0o644,
    ) {}

    public function toString(): string
    {
        return "has entry {$this->export($this->location)} "
            . "with contents {$this->export($this->contents)} "
            . "and mode {$this->export($this->mode)}";
    }

    #[\Override]
    protected function matches(mixed $other): bool
    {
        return $other instanceof Storage
            && $other->exists($this->location)
            && $other->read($this->location) === $this->contents
            && $other->mode($this->location) === $this->mode;
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

        if (!$other->exists($this->location)) {
            return "\nBut no entry exists at location {$this->export($this->location)}";
        }

        return "\nExpected contents: {$this->export($this->contents)}"
            . "\nBut was: {$this->export($other->read($this->location))}"
            . "\nExpected mode: {$this->export($this->mode)}"
            . "\nBut was: {$this->export($other->mode($this->location))}";
    }

    private function export(mixed $value): string
    {
        return var_export($value, true);
    }
}
