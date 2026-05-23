<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Settings;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;

final readonly class FakeSettings implements Settings
{
    /** @param array<string, Value> $values */
    public function __construct(private array $values) {}

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    public function value(string $name): Value
    {
        if (!$this->has($name)) {
            throw new SheriffException(sprintf('Unknown config key "%s"', $name));
        }

        return $this->values[$name];
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_keys($this->values);
    }
}
