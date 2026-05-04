<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\EnvVar;

use Haspadar\Sheriff\Config\Config;
use Haspadar\Sheriff\SheriffException;

/**
 * Environment variable required on a developer machine
 */
interface EnvVar
{
    public function name(): string;

    public function url(): string;

    /** @throws SheriffException */
    public function enabled(Config $config): bool;
}
