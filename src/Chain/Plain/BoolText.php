<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Plain;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Override;

/**
 * Renders a BoolValue as a plain "true" or "false" literal.
 *
 * Format-neutral source for boolean configuration values. The result is the
 * same in neon, json, php and most other formats, so callers do not need a
 * format-specific renderer to embed a boolean into a template.
 *
 * Example:
 *
 *     (new BoolText(new BoolValue(true)))->rendered(); // "true"
 */
final readonly class BoolText implements Op
{
    /**
     * Initializes with the boolean value to render.
     *
     * @param BoolValue $value Boolean payload rendered as a plain literal
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
