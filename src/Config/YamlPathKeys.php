<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

use Haspadar\Sheriff\SheriffException;

/**
 * Resolves include and exclude lists from .sheriff.yaml override/append sections.
 *
 * Cascades into DefaultConfig so all derived path keys reflect the project layout.
 */
final readonly class YamlPathKeys
{
    /**
     * Initializes with override and append maps alongside the base defaults.
     *
     * @param array<string, mixed> $overrides Values that replace the matching defaults
     * @param array<string, mixed> $appends Values to append to matching list defaults
     * @param DefaultConfig $defaults Base configuration providing built-in key defaults
     */
    public function __construct(
        private array $overrides,
        private array $appends,
        private DefaultConfig $defaults,
    ) {}

    /**
     * Resolves the effective PHP source directories after overrides and appends.
     *
     * @throws SheriffException
     * @return list<string>
     */
    public function phpSrc(): array
    {
        $result = array_key_exists('php.src', $this->overrides) && is_array($this->overrides['php.src'])
            ? $this->toStringList(array_values($this->overrides['php.src']), 'override.php.src')
            : $this->toStringList($this->defaults->list('php.src'), 'php.src');

        if (array_key_exists('php.src', $this->appends) && is_array($this->appends['php.src'])) {
            $extra = $this->toStringList(array_values($this->appends['php.src']), 'append.php.src');
            $result = array_values(array_unique(array_merge($result, $extra)));
        }

        return $result;
    }

    /**
     * Resolves the effective infrastructure exclude patterns after overrides and appends.
     *
     * @throws SheriffException
     * @return list<string>
     */
    public function infraExclude(): array
    {
        $result = array_key_exists('infra.exclude', $this->overrides) && is_array($this->overrides['infra.exclude'])
            ? $this->toStringList(array_values($this->overrides['infra.exclude']), 'override.infra.exclude')
            : $this->toStringList($this->defaults->list('infra.exclude'), 'infra.exclude');

        if (array_key_exists('infra.exclude', $this->appends) && is_array($this->appends['infra.exclude'])) {
            $extra = $this->toStringList(array_values($this->appends['infra.exclude']), 'append.infra.exclude');
            $result = array_values(array_unique(array_merge($result, $extra)));
        }

        return $result;
    }

    /**
     * Converts a mixed list to a validated list of strings.
     *
     * @param list<mixed> $value
     * @throws SheriffException
     * @return list<string>
     */
    private function toStringList(array $value, string $key): array
    {
        $result = [];

        foreach ($value as $i => $item) {
            if (!is_string($item)) {
                throw new SheriffException(
                    sprintf('"%s" must be a list of strings, got %s at index %d', $key, get_debug_type($item), $i),
                );
            }

            $result[] = $item;
        }

        return $result;
    }
}
