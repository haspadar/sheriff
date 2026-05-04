<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ScalarValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;

/**
 * Replaces a scalar configuration value at the given key.
 *
 * Example:
 *
 *     new OverrideScalar('phpstan.level', new IntValue(8));
 */
final readonly class OverrideScalar implements Patch
{
    /**
     * Initializes with the target key and the replacement scalar value.
     *
     * @param string $key Configuration key whose scalar value is replaced
     * @param ScalarValue $value Scalar replacing the base value at the key
     */
    public function __construct(private string $key, private ScalarValue $value) {}

    #[Override]
    public function key(): string
    {
        return $this->key;
    }

    #[Override]
    public function applied(Value $base): Value
    {
        return $this->value;
    }
}
