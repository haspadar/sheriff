<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Envs;

use Haspadar\Sheriff\SheriffException;

/**
 * Environment variables for CI workflows.
 */
interface Envs
{
    /**
     * @throws SheriffException
     * @return array<string, string> variable name => shell command
     */
    public function vars(): array;
}
