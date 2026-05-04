<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\SheriffException;

/**
 * A collection of quality checks
 */
interface Checks
{
    /**
     * Returns all checks in this collection.
     *
     * @throws SheriffException
     * @return iterable<Check>
     */
    public function all(): iterable;
}
