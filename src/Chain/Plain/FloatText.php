<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Plain;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Renders a FloatValue as its plain decimal representation.
 *
 * Special floats (INF, -INF, NAN) cannot be embedded into a configuration
 * template safely, so rendering them is a configuration error.
 *
 * Example:
 *
 *     (new FloatText(new FloatValue(0.5)))->rendered(); // "0.5"
 */
final readonly class FloatText implements Op
{
    /**
     * Initializes with the float value to render.
     *
     * @param FloatValue $value Float payload rendered as a plain decimal
     */
    public function __construct(private FloatValue $value) {}

    #[Override]
    public function rendered(): string
    {
        $raw = $this->value->raw;

        if (!is_finite($raw)) {
            throw new SheriffException(
                sprintf('FloatText cannot render non-finite value "%s"', (string) $raw),
            );
        }

        return (string) $raw;
    }
}
