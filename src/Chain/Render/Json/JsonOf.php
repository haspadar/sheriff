<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Json;

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
 * Picks the matching Json renderer for a configuration value.
 *
 * Container renderers (`JsonList`, `JsonTree`) carry the surrounding indent
 * depth so nested structures stay aligned with their parent.
 *
 * Example:
 *
 *     (new JsonOf(new BoolValue(true)))->renderer()->rendered(); // "true"
 */
final readonly class JsonOf
{
    /**
     * Initializes with the value to render and the indent depth used by container renderers.
     *
     * @param Value $value Configuration value resolved into a json-format renderer
     * @param int $depth Indent depth threaded into list / tree renderers; ignored by scalar renderers
     */
    public function __construct(private Value $value, private int $depth = 0) {}

    /**
     * Returns the Rendered op that knows how to render this value as json.
     *
     * @throws TypeError
     */
    public function renderer(): Rendered
    {
        return match (true) {
            $this->value instanceof BoolValue => new JsonBool($this->value),
            $this->value instanceof IntValue => new JsonInt($this->value),
            $this->value instanceof FloatValue => new JsonFloat($this->value),
            $this->value instanceof StringValue => new JsonString($this->value),
            $this->value instanceof ListValue => new JsonList($this->value, $this->depth),
            $this->value instanceof TreeValue => new JsonTree($this->value, $this->depth),
            default => throw new TypeError(
                sprintf('Unsupported Value subtype: %s', $this->value::class),
            ),
        };
    }
}
