<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Override;

/**
 * Renders a BoolValue as a neon `true` or `false` literal.
 *
 * Example:
 *
 *     (new NeonBool(new BoolValue(true)))->rendered(); // "true"
 */
final readonly class NeonBool implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param BoolValue $value Boolean payload rendered as a neon literal
     */
    public function __construct(private BoolValue $value) {}

    #[Override]
    public function rendered(): string
    {
        return $this->value->raw
            ? 'true'
            : 'false';
    }
}
