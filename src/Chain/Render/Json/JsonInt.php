<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Override;

/**
 * Renders an IntValue as a json integer literal.
 *
 * Example:
 *
 *     (new JsonInt(new IntValue(80)))->rendered(); // "80"
 */
final readonly class JsonInt implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param IntValue $value Integer payload rendered as a json literal
     */
    public function __construct(private IntValue $value) {}

    #[Override]
    public function rendered(): string
    {
        return (string) $this->value->raw;
    }
}
