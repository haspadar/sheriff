<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Override;
use UnexpectedValueException;

/**
 * Renders a FloatValue as a neon floating-point literal.
 *
 * Example:
 *
 *     (new NeonFloat(new FloatValue(0.5)))->rendered(); // "0.5"
 */
final readonly class NeonFloat implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param FloatValue $value Float payload rendered as a neon literal
     */
    public function __construct(private FloatValue $value) {}

    #[Override]
    public function rendered(): string
    {
        if (!is_finite($this->value->raw)) {
            throw new UnexpectedValueException(
                sprintf('NeonFloat cannot render non-finite payload: %s', var_export($this->value->raw, true)),
            );
        }

        $rendered = (string) $this->value->raw;

        return str_contains($rendered, '.')
            ? $rendered
            : sprintf('%s.0', $rendered);
    }
}
