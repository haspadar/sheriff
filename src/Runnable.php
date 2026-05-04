<?php

declare(strict_types=1);

namespace Haspadar\Sheriff;

use UnexpectedValueException;

/** A unit of work that can be executed. */
interface Runnable
{
    /** @throws SheriffException|UnexpectedValueException */
    public function run(): void;
}
