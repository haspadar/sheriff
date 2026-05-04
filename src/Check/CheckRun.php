<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\SheriffException;

/**
 * Executes a single check and captures its result.
 */
final readonly class CheckRun
{
    /**
     * Initializes with the check to run and verbosity flag.
     *
     * @param Check $check Check definition to execute
     * @param CliOption $verbose When enabled, streams output instead of capturing
     */
    public function __construct(private Check $check, private CliOption $verbose) {}

    /**
     * Runs the check command and returns its result.
     *
     * @throws SheriffException
     */
    public function result(): CheckResult
    {
        $command = sprintf('bash %s', escapeshellarg($this->check->command()));
        $start = microtime(true);

        if ($this->verbose->enabled()) {
            passthru($command, $status);

            return new CheckResult(
                $status,
                '',
                microtime(true) - $start,
            );
        }

        $output = [];
        exec("{$command} 2>&1", $output, $status);

        /** @var list<string> $lines */
        $lines = $output;

        return new CheckResult(
            $status,
            implode("\n", $lines),
            microtime(true) - $start,
        );
    }
}
