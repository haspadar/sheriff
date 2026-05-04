<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Config;

use Haspadar\Sheriff\Config\Config;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * Asserts that a Config instance returns an expected value for a given key.
 */
final class HasConfigYamlKey extends Constraint
{
    public function __construct(
        private readonly string $key,
        private readonly mixed $expected,
    ) {}

    protected function matches(mixed $other): bool
    {
        if (!$other instanceof Config) {
            return false;
        }

        return $this->actual($other) === $this->expected;
    }

    public function toString(): string
    {
        return 'has ' . $this->key . ' === ' . var_export($this->expected, true);
    }

    protected function additionalFailureDescription(mixed $other): string
    {
        if (!$other instanceof Config) {
            return "\nExpected a Config instance";
        }

        return "\nExpected: " . var_export($this->expected, true)
            . "\nBut was:  " . var_export($this->actual($other), true);
    }

    /** @return list<scalar>|scalar */
    private function actual(Config $config): mixed
    {
        $list = $config->list($this->key);

        if (is_scalar($this->expected)) {
            return count($list) === 1 ? $list[0] : $list;
        }

        return $list;
    }
}
