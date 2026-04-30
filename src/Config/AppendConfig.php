<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Config;

use Haspadar\Piqule\PiquleException;
use Override;

/**
 * Appends values to existing configuration lists without replacing them.
 *
 * Example:
 *
 *     new AppendConfig(new DefaultConfig(), [
 *         'phpstan.neon_includes' => ['../../rules.neon'],
 *         'infra.exclude' => ['legacy'],
 *     ]);
 */
final readonly class AppendConfig implements Config
{
    /**
     * Initializes with a base config and values to append.
     *
     * @param Config $defaults Underlying configuration to extend
     * @param array<string, mixed> $appends Values to append to each matching list key
     */
    public function __construct(private Config $defaults, private array $appends) {}

    #[Override]
    public function has(string $name): bool
    {
        return $this->defaults->has($name);
    }

    #[Override]
    public function list(string $name): array
    {
        if (!$this->defaults->has($name)) {
            throw new PiquleException(
                sprintf('Unknown config key "%s"', $name),
            );
        }

        if (!array_key_exists($name, $this->appends)) {
            return $this->defaults->list($name);
        }

        $items = $this->appends[$name];

        if (!is_array($items) || !array_is_list($items)) {
            throw new PiquleException(
                sprintf('Append "%s" must be a list<scalar>', $name),
            );
        }

        $scalars = [];

        foreach ($items as $item) {
            if (!is_scalar($item)) {
                throw new PiquleException(
                    sprintf('Append "%s" must contain only scalars', $name),
                );
            }

            $scalars[] = $item;
        }

        return [...$this->defaults->list($name), ...$scalars];
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
