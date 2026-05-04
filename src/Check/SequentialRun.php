<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Output\Output;
use Haspadar\Sheriff\Runnable;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Runs checks one by one, stopping on the first failure.
 */
final readonly class SequentialRun implements Runnable
{
    /**
     * Initializes with checks, output channel, and verbosity option.
     *
     * @param Checks $checks Checks to execute in order
     * @param Output $output Channel to stream progress messages to
     * @param CliOption $verbose When enabled, each check's output is streamed live
     */
    public function __construct(
        private Checks $checks,
        private Output $output,
        private CliOption $verbose,
    ) {}

    #[Override]
    public function run(): void
    {
        /** @var list<Check> $all */
        $all = iterator_to_array($this->checks->all());
        $report = new CheckReport($this->output, count($all));
        $number = 0;
        $start = microtime(true);

        foreach ($all as $check) {
            $number++;
            $report->started($check->name(), $number);
            $result = (new CheckRun($check, $this->verbose))->result();

            if (!$result->passed()) {
                if ($result->output() !== '') {
                    echo "{$result->output()}\n";
                }

                $report->failed($check->name(), $result->elapsed());
                $report->failed('Checks failed', microtime(true) - $start);

                throw new SheriffException('');
            }

            $report->passed($check->name(), $result->elapsed());
        }

        $report->passed('All checks passed', microtime(true) - $start);
    }
}
