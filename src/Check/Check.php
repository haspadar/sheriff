<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

/**
 * A named quality check with a shell command
 */
interface Check
{
    /**
     * Check name, e.g. "phpstan" or "phpunit"
     */
    public function name(): string;

    /**
     * Absolute path to the command.sh file
     */
    public function command(): string;
}
