<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Replaces specific configuration values while preserving all other defaults.
 */
final readonly class OverrideConfig implements Config
{
    /**
     * Initializes with a base config and override values.
     *
     * @param Config $defaults Underlying configuration to override
     * @param array<string, mixed> $overrides Values that replace the matching defaults
     */
    public function __construct(private Config $defaults, private array $overrides) {}

    #[Override]
    public function has(string $name): bool
    {
        return $this->defaults->has($name);
    }

    #[Override]
    public function list(string $name): array
    {
        if (!$this->defaults->has($name)) {
            throw new SheriffException(
                sprintf('Unknown config key "%s"', $name),
            );
        }

        if (!array_key_exists($name, $this->overrides)) {
            return $this->defaults->list($name);
        }

        return $this->normalizedValue($this->overrides[$name], $name);
    }

    /**
     * Converts a raw override value to a validated scalar list.
     *
     * @throws SheriffException
     * @return list<scalar>
     */
    private function normalizedValue(mixed $value, string $name): array
    {
        if (is_scalar($value)) {
            return [$value];
        }

        if (!is_array($value) || !array_is_list($value)) {
            throw new SheriffException(
                sprintf('Override "%s" must be scalar or list<scalar>', $name),
            );
        }

        foreach ($value as $item) {
            if (!is_scalar($item)) {
                throw new SheriffException(
                    sprintf('Override "%s" must contain only scalars', $name),
                );
            }
        }

        return array_values(array_filter($value, static fn($item) => is_scalar($item)));
    }

    #[Override]
    public function toArray(): array
    {
        $result = $this->defaults->toArray();

        foreach (array_keys($result) as $key) {
            $result[$key] = $this->list($key);
        }

        return $result;
    }
}
