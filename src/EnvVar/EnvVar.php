<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\EnvVar;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\SheriffException;

/**
 * Environment variable required on a developer machine.
 */
interface EnvVar
{
    public function name(): string;

    public function url(): string;

    /** @throws SheriffException */
    public function enabled(Settings $settings): bool;
}
