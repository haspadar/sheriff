<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Settings\Value;

use Haspadar\Piqule\PiquleException;
use TypeError;

/**
 * Wraps a raw PHP value parsed from YAML in the matching Value implementation.
 *
 * Example:
 *
 *     (new RawValue(true))->value(); // BoolValue
 *     (new RawValue([1, 2]))->value(); // ListValue of IntValue
 *     (new RawValue(['k' => 1]))->value(); // TreeValue
 */
final readonly class RawValue
{
    /**
     * Initializes with the raw payload.
     *
     * @param mixed $raw Arbitrary scalar, list, or associative array from YAML
     */
    public function __construct(private mixed $raw) {}

    /**
     * Returns the Value implementation matching the payload's runtime type.
     *
     * @throws PiquleException
     * @throws TypeError
     */
    public function value(): Value
    {
        return match (true) {
            $this->raw === null => throw new PiquleException(
                'Null config values are not supported; declare an explicit default',
            ),
            is_bool($this->raw) => new BoolValue($this->raw),
            is_int($this->raw) => new IntValue($this->raw),
            is_float($this->raw) => new FloatValue($this->raw),
            is_string($this->raw) => new StringValue($this->raw),
            is_array($this->raw) => $this->fromArray($this->raw),
            default => throw new TypeError(
                sprintf('Unsupported config value type: %s', get_debug_type($this->raw)),
            ),
        };
    }

    /**
     * Wraps an array payload as a ListValue or TreeValue depending on its shape.
     *
     * @param array<int|string, mixed> $items
     * @throws PiquleException
     * @throws TypeError
     */
    private function fromArray(array $items): Value
    {
        if (array_is_list($items)) {
            return new ListValue(
                array_map(static fn(mixed $item): Value => (new self($item))->value(), $items),
            );
        }

        $entries = [];

        /** @var mixed $item */
        foreach ($items as $key => $item) {
            $entries[(string) $key] = (new self($item))->value();
        }

        return new TreeValue($entries);
    }
}
