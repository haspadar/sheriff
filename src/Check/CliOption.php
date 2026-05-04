<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\SheriffException;

/**
 * A boolean CLI option that can be enabled or disabled
 */
interface CliOption
{
    /**
     * Whether this option is enabled
     *
     * @throws SheriffException
     */
    public function enabled(): bool;
}
