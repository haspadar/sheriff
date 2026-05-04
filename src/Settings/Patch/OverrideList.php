<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;

/**
 * Replaces a list configuration value at the given key in full.
 *
 * Example:
 *
 *     new OverrideList('phpstan.paths', new ListValue([new StringValue('lib')]));
 */
final readonly class OverrideList implements Patch
{
    /**
     * Initializes with the target key and the replacement list value.
     *
     * @param string $key Configuration key whose list value is replaced
     * @param ListValue $value List replacing the base value at the key
     */
    public function __construct(private string $key, private ListValue $value) {}

    #[Override]
    public function key(): string
    {
        return $this->key;
    }

    #[Override]
    public function applied(Value $base): Value
    {
        return $this->value;
    }
}
