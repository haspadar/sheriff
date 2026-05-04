<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Plain;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Override;

/**
 * Renders an IntValue as its decimal string representation.
 *
 * Example:
 *
 *     (new IntText(new IntValue(8)))->rendered(); // "8"
 */
final readonly class IntText implements Op
{
    /**
     * Initializes with the integer value to render.
     *
     * @param IntValue $value Integer payload rendered as a decimal literal
     */
    public function __construct(private IntValue $value) {}

    #[Override]
    public function rendered(): string
    {
        return (string) $this->value->raw;
    }
}
