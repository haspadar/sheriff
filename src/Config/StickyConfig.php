<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

use Closure;
use Override;
use stdClass;

/**
 * Caches the result of a config-producing closure on first access.
 *
 * Example:
 *
 *     new StickyConfig(fn() => new DefaultConfig([], [], $paths));
 */
final readonly class StickyConfig implements Config
{
    private stdClass $cache;

    /**
     * Initializes with a config factory closure.
     *
     * @param Closure(): Config $origin Factory invoked on first access to produce the cached config
     */
    public function __construct(private Closure $origin)
    {
        $this->cache = new stdClass();
    }

    #[Override]
    public function has(string $name): bool
    {
        return $this->config()->has($name);
    }

    #[Override]
    public function list(string $name): array
    {
        return $this->config()->list($name);
    }

    #[Override]
    public function toArray(): array
    {
        return $this->config()->toArray();
    }

    private function config(): Config
    {
        if (!isset($this->cache->value)) {
            $this->cache->value = ($this->origin)();
        }

        $config = $this->cache->value;
        assert($config instanceof Config);

        return $config;
    }
}
