<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use TypeError;

/**
 * Picks the matching Neon renderer for a configuration value.
 *
 * Example:
 *
 *     (new NeonOf(new BoolValue(true)))->renderer()->rendered(); // "true"
 */
final readonly class NeonOf
{
    /**
     * Initializes with the value to render.
     *
     * @param Value $value Configuration value resolved into a neon-format renderer
     */
    public function __construct(private Value $value) {}

    /**
     * Returns the Rendered op that knows how to render this value as neon.
     *
     * @throws TypeError
     */
    public function renderer(): Rendered
    {
        return match (true) {
            $this->value instanceof BoolValue => new NeonBool($this->value),
            $this->value instanceof IntValue => new NeonInt($this->value),
            $this->value instanceof FloatValue => new NeonFloat($this->value),
            $this->value instanceof StringValue => new NeonString($this->value),
            $this->value instanceof ListValue => new NeonList($this->value),
            $this->value instanceof TreeValue => new NeonTree($this->value),
            default => throw new TypeError(
                sprintf('Unsupported Value subtype: %s', $this->value::class),
            ),
        };
    }
}
