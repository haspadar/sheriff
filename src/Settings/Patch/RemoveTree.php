<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Removes the named keys from a tree configuration value at the given key.
 *
 * Example:
 *
 *     new RemoveTree('phpstan.parameters', ['reportUnmatchedIgnoredErrors']);
 */
final readonly class RemoveTree implements Patch
{
    /**
     * Initializes with the target key and the names of entries to drop.
     *
     * @param string $key Configuration key whose tree value loses the named entries
     * @param list<string> $keys Names of entries removed from the base tree when present
     */
    public function __construct(private string $key, private array $keys) {}

    #[Override]
    public function key(): string
    {
        return $this->key;
    }

    #[Override]
    public function applied(Value $base): Value
    {
        if (!$base instanceof TreeValue) {
            throw new TypeError(
                sprintf('RemoveTree expects TreeValue at "%s"', $this->key),
            );
        }

        $entries = $base->entries;

        foreach ($this->keys as $name) {
            unset($entries[$name]);
        }

        return new TreeValue($entries);
    }
}
