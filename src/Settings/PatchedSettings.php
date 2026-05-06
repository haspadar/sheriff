<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings;

use Haspadar\Sheriff\Settings\Value\Value;
use Override;

/**
 * Settings decorated with one Patch applied to its targeted key.
 *
 * Example:
 *
 *     new PatchedSettings(new DefaultSettings(), $patch);
 */
final readonly class PatchedSettings implements Settings
{
    /**
     * Initializes with the base settings and the patch to apply.
     *
     * @param Settings $base Underlying settings exposing the original value
     * @param Patch $patch Operation applied to the value at the patch key
     */
    public function __construct(private Settings $base, private Patch $patch) {}

    #[Override]
    public function has(string $name): bool
    {
        return $this->base->has($name);
    }

    #[Override]
    public function value(string $name): Value
    {
        return $name === $this->patch->key()
            ? $this->patch->applied($this->base->value($name))
            : $this->base->value($name);
    }

    #[Override]
    public function keys(): array
    {
        return $this->base->keys();
    }
}
