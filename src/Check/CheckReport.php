<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Output\Output;

/**
 * Formats check execution progress to output.
 */
final readonly class CheckReport
{
    private const int TITLE_WIDTH = 20;

    /**
     * Initializes with output channel and total check count.
     *
     * @param Output $output Channel to write progress messages to
     * @param int $total Total number of checks in the run
     */
    public function __construct(private Output $output, private int $total) {}

    /**
     * Reports a check starting.
     *
     * @param string $name Human-readable check name
     * @param int $number One-based ordinal of this check within the run
     */
    public function started(string $name, int $number): void
    {
        $this->output->muted(
            $this->total > 1
                ? sprintf('[RUN]  %-*s%5s', self::TITLE_WIDTH, $name, "{$number}/{$this->total}")
                : "[RUN]  {$name}",
        );
    }

    /**
     * Reports a check that passed.
     *
     * @param string $name Human-readable check name
     * @param float $elapsed Wall-clock duration in seconds
     */
    public function passed(string $name, float $elapsed): void
    {
        $this->output->success(
            sprintf('[OK]   %-*s%5s', self::TITLE_WIDTH, $name, (new ElapsedTime($elapsed))->formatted()),
        );
    }

    /**
     * Reports a check that failed.
     *
     * @param string $name Human-readable check name
     * @param float $elapsed Wall-clock duration in seconds
     */
    public function failed(string $name, float $elapsed): void
    {
        $this->output->error(
            sprintf('[FAIL] %-*s%5s', self::TITLE_WIDTH, $name, (new ElapsedTime($elapsed))->formatted()),
        );
    }
}
