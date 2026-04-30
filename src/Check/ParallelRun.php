<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Check;

use Haspadar\Piqule\Output\Output;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Runnable;
use Override;

/**
 * Runs checks in parallel via proc_open, respecting dependencies.
 */
final readonly class ParallelRun implements Runnable
{
    private const int STDOUT_FD = 1;

    private const int STDERR_FD = 2;

    private const array DEPENDS_ON = [
        'sonar' => ['phpunit'],
        'infection' => ['phpunit'],
    ];

    /**
     * Initializes with checks, output channel, and verbosity option.
     *
     * @param Checks $checks Checks to execute in parallel
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
        $start = microtime(true);
        $independent = [];
        $dependent = [];

        foreach ($this->checks->all() as $check) {
            if (array_key_exists($check->name(), self::DEPENDS_ON)) {
                $dependent[] = $check;
            } else {
                $independent[] = $check;
            }
        }

        $total = count($independent) + count($dependent);
        $report = new CheckReport($this->output, $total);
        $failed = $this->batch($independent, 0, $report);

        if (!$failed && $dependent !== []) {
            $failed = $this->batch($dependent, count($independent), $report);
        }

        if ($failed) {
            $report->failed('Checks failed', microtime(true) - $start);

            throw new PiquleException('');
        }

        $report->passed('All checks passed', microtime(true) - $start);
    }

    /**
     * Launches a batch of checks and collects results.
     *
     * @param list<Check> $batch Checks to launch together
     * @param int $offset Ordinal offset of the first check in the overall run
     * @param CheckReport $report Reporter used to announce starts and outcomes
     * @throws PiquleException
     */
    private function batch(array $batch, int $offset, CheckReport $report): bool
    {
        $handles = [];

        foreach ($batch as $index => $check) {
            $handle = $this->spawn($check);

            if (!is_array($handle)) {
                throw new PiquleException("Failed to start: {$check->name()}");
            }

            $report->started($check->name(), $offset + $index + 1);
            $handles[] = $handle;
        }

        return $this->collect($handles, $report);
    }

    /**
     * Collects results from running processes and reports each one.
     *
     * @param list<array{proc: resource, stdout: resource, stderr: resource, check: Check, start: float}> $handles Running process records produced by spawn()
     * @param CheckReport $report Reporter used to announce outcomes
     * @throws PiquleException
     */
    private function collect(array $handles, CheckReport $report): bool
    {
        $failed = false;

        foreach ((new ProcessPool())->results($handles) as $entry) {
            $result = $entry['result'];

            if (!$result->passed() || $this->verbose->enabled()) {
                if ($result->output() !== '') {
                    echo $result->output();
                }
            }

            if ($result->passed()) {
                $report->passed($entry['check']->name(), $entry['elapsed']);
            } else {
                $report->failed($entry['check']->name(), $entry['elapsed']);
                $failed = true;
            }
        }

        return $failed;
    }

    /**
     * Spawns a check process.
     *
     * @param Check $check Check to launch as a background process
     * @return array{proc: resource, stdout: resource, stderr: resource, check: Check, start: float}|false
     */
    private function spawn(Check $check): array|false
    {
        $stdout = tmpfile();
        $stderr = tmpfile();

        if (!is_resource($stdout) || !is_resource($stderr)) {
            return false;
        }

        $proc = proc_open(
            sprintf('bash %s', escapeshellarg($check->command())),
            [self::STDOUT_FD => $stdout, self::STDERR_FD => $stderr],
            $pipes,
        );

        if (!is_resource($proc)) {
            return false;
        }

        return [
            'proc' => $proc,
            'stdout' => $stdout,
            'stderr' => $stderr,
            'check' => $check,
            'start' => microtime(true),
        ];
    }
}
