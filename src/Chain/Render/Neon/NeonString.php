<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Override;

/**
 * Renders a StringValue as a neon string literal in double quotes.
 *
 * Example:
 *
 *     (new NeonString(new StringValue('1G')))->rendered(); // "\"1G\""
 */
final readonly class NeonString implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param StringValue $value String payload rendered as a quoted neon literal
     */
    public function __construct(private StringValue $value) {}

    #[Override]
    public function rendered(): string
    {
        $escaped = strtr(
            addcslashes($this->value->raw, '"\\'),
            ["\n" => '\\n', "\r" => '\\r', "\t" => '\\t'],
        );

        return sprintf('"%s"', $escaped);
    }
}
