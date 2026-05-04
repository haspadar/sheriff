<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Plain;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Override;

/**
 * Renders a StringValue as its raw string payload without quotes.
 *
 * Format-neutral source for string configuration values. Callers that need a
 * quoted or escaped representation should compose this with a format-specific
 * renderer instead of relying on StringText for escaping.
 *
 * Example:
 *
 *     (new StringText(new StringValue('1G')))->rendered(); // "1G"
 */
final readonly class StringText implements Op
{
    /**
     * Initializes with the string value to render.
     *
     * @param StringValue $value String payload rendered verbatim, without quoting
     */
    public function __construct(private StringValue $value) {}

    #[Override]
    public function rendered(): string
    {
        return $this->value->raw;
    }
}
