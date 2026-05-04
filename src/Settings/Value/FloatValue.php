<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Configuration value holding a floating-point number.
 *
 * Example:
 *
 *     new FloatValue(0.5);
 */
final readonly class FloatValue implements ScalarValue
{
    /**
     * Initializes with the float payload.
     *
     * @param float $raw Floating-point number carried by this value
     */
    public function __construct(public float $raw) {}
}
