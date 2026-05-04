<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Configuration value holding an ordered list of nested values.
 *
 * Example:
 *
 *     new ListValue([new StringValue('src'), new StringValue('tests')]);
 */
final readonly class ListValue implements Value
{
    /**
     * Initializes with the list payload.
     *
     * @param list<Value> $children Ordered nested values carried by this list
     */
    public function __construct(public array $children) {}
}
