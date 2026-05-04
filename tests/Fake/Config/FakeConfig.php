<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Config;

use Haspadar\Sheriff\Config\Config;

final class FakeConfig implements Config
{
    /**
     * @param array<string, list<scalar>> $data
     */
    public function __construct(private array $data) {}

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    public function list(string $name): array
    {
        if (!$this->has($name)) {
            return [];
        }

        return $this->data[$name];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
