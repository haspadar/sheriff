<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Output;

/**
 * Writes user-facing messages to an output channel
 */
interface Output
{
    /**
     * Emits an informational message
     */
    public function info(string $text): void;

    /**
     * Emits a success message
     */
    public function success(string $text): void;

    /**
     * Emits an error message
     */
    public function error(string $text): void;

    /**
     * Emits a muted (secondary) message
     */
    public function muted(string $text): void;
}
