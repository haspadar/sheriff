<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Appends entries to the end of a list configuration value at the given key.
 *
 * Example:
 *
 *     new AppendList('infra.exclude', new ListValue([new StringValue('dist')]));
 */
final readonly class AppendList implements Patch
{
    /**
     * Initializes with the target key and the list of entries to append.
     *
     * @param string $key Configuration key whose list value receives the extra entries
     * @param ListValue $extra Entries appended to the end of the base list
     */
    public function __construct(private string $key, private ListValue $extra) {}

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
                sprintf('AppendList expects ListValue at "%s"', $this->key),
            );
        }

        return new ListValue([...$base->children, ...$this->extra->children]);
    }
}
