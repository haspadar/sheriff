<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Adds new keys to a tree configuration value without overwriting existing entries.
 *
 * Example:
 *
 *     new AppendTree('phpstan.parameters', new TreeValue([
 *         'reportUnmatchedIgnoredErrors' => new BoolValue(true),
 *     ]));
 */
final readonly class AppendTree implements Patch
{
    /**
     * Initializes with the target key and the entries to add to the base tree.
     *
     * @param string $key Configuration key whose tree value receives the extra entries
     * @param TreeValue $extra Entries added to the base tree only when their key is absent
     */
    public function __construct(private string $key, private TreeValue $extra) {}

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
                sprintf('AppendTree expects TreeValue at "%s"', $this->key),
            );
        }

        $entries = $base->entries;

        foreach ($this->extra->entries as $key => $value) {
            $entries[$key] ??= $value;
        }

        return new TreeValue($entries);
    }
}
