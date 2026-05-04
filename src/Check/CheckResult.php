<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

/**
 * Outcome of a single check execution.
 */
final readonly class CheckResult
{
    /**
     * Initializes with exit status, captured output, and elapsed time.
     *
     * @param int $status Process exit status (0 means success)
     * @param string $output Captured stdout and stderr combined
     * @param float $elapsed Wall-clock duration in seconds
     */
    public function __construct(
        private int $status,
        private string $output,
        private float $elapsed,
    ) {}

    /** Whether the check exited successfully. */
    public function passed(): bool
    {
        return $this->status === 0;
    }

    /** Captured stdout/stderr text. */
    public function output(): string
    {
        return $this->output;
    }

    /** Wall-clock seconds the check took. */
    public function elapsed(): float
    {
        return $this->elapsed;
    }
}
