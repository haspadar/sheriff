<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Removes the named entries from a list configuration value at the given key.
 *
 * Example:
 *
 *     new RemoveList('phpstan.checked_exceptions', new ListValue([new StringValue('\\Throwable')]));
 */
final readonly class RemoveList implements Patch
{
    /**
     * Initializes with the target key and the entries to drop from the base list.
     *
     * @param string $key Configuration key whose list value loses the named entries
     * @param ListValue $items Entries removed from the base list when present
     */
    public function __construct(private string $key, private ListValue $items) {}

    #[Override]
    public function key(): string
    {
        return $this->key;
    }

    #[Override]
    public function applied(Value $base): Value
    {
        if (!$base instanceof ListValue) {
            throw new TypeError(
                sprintf('RemoveList expects ListValue at "%s"', $this->key),
            );
        }

        $blocked = array_map('serialize', $this->items->children);

        $kept = array_filter(
            $base->children,
            static fn(Value $child): bool => !in_array(serialize($child), $blocked, true),
        );

        return new ListValue(array_values($kept));
    }
}
