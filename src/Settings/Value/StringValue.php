<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Configuration value holding a string.
 *
 * Example:
 *
 *     new StringValue('1G');
 */
final readonly class StringValue implements ScalarValue
{
    /**
     * Initializes with the string payload.
     *
     * @param string $raw String carried by this value
     */
    public function __construct(public string $raw) {}
}
