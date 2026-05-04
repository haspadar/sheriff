<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Configuration value holding an integer.
 *
 * Example:
 *
 *     new IntValue(8);
 */
final readonly class IntValue implements ScalarValue
{
    /**
     * Initializes with the integer payload.
     *
     * @param int $raw Integer carried by this value
     */
    public function __construct(public int $raw) {}
}
