<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings;

use Haspadar\Sheriff\Settings\Value\Value;

/**
 * One operation derived from .sheriff.yaml that modifies a configuration value.
 */
interface Patch
{
    /**
     * Returns the configuration key targeted by this patch.
     */
    public function key(): string;

    /**
     * Returns a new value produced by applying this patch to the base value.
     */
    public function applied(Value $base): Value;
}
