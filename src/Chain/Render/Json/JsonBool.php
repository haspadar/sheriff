<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Override;

/**
 * Renders a BoolValue as a json `true` or `false` literal.
 *
 * Example:
 *
 *     (new JsonBool(new BoolValue(true)))->rendered(); // "true"
 */
final readonly class JsonBool implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param BoolValue $value Boolean payload rendered as a json literal
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
