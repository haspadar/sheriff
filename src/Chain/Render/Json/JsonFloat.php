<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Override;
use UnexpectedValueException;

/**
 * Renders a FloatValue as a json number literal.
 *
 * Json forbids `INF` / `NAN`, so non-finite payloads are rejected loudly.
 *
 * Example:
 *
 *     (new JsonFloat(new FloatValue(0.5)))->rendered(); // "0.5"
 */
final readonly class JsonFloat implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param FloatValue $value Float payload rendered as a json number
     */
    public function __construct(private FloatValue $value) {}

    #[Override]
    public function rendered(): string
    {
        if (!is_finite($this->value->raw)) {
            throw new UnexpectedValueException(
                sprintf('JsonFloat cannot render non-finite payload: %s', (string) $this->value->raw),
            );
        }

        $rendered = (string) $this->value->raw;

        return str_contains($rendered, '.')
            ? $rendered
            : sprintf('%s.0', $rendered);
    }
}
