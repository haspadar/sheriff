<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Override;

/**
 * Renders a StringValue as a json string literal in double quotes.
 *
 * Uses `json_encode` with `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR`
 * so paths and non-ASCII text round-trip readably and malformed UTF-8 surfaces as a JsonException.
 *
 * Example:
 *
 *     (new JsonString(new StringValue('json5')))->rendered(); // "\"json5\""
 */
final readonly class JsonString implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param StringValue $value String payload rendered as a quoted json literal
     */
    public function __construct(private StringValue $value) {}

    #[Override]
    public function rendered(): string
    {
        return json_encode(
            $this->value->raw,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
