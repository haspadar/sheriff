<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

/**
 * Polls a set of running processes and collects their results.
 */
final readonly class ProcessPool
{
    private const int POLL_INTERVAL_USEC = 50_000;

    private const int UNKNOWN_EXIT_CODE = -1;

    /**
     * Waits for all processes to finish and yields each result.
     *
     * @param list<array{proc: resource, stdout: resource, stderr: resource, check: Check, start: float}> $handles Running process records to drain
     * @return iterable<array{check: Check, result: CheckResult, elapsed: float}>
     */
    public function results(array $handles): iterable
    {
        $pending = $handles;

        while ($pending !== []) {
            foreach ($pending as $i => $handle) {
                $info = proc_get_status($handle['proc']);

                if ($info['running']) {
                    continue;
                }

                $elapsed = microtime(true) - $handle['start'];

                yield $this->collect($handle, $info['exitcode'], $elapsed);

                unset($pending[$i]);
            }

            usleep(self::POLL_INTERVAL_USEC);
        }
    }

    /**
     * Closes a finished process and returns its result.
     *
     * @param array{proc: resource, stdout: resource, stderr: resource, check: Check, start: float} $handle Process record to finalize
     * @param int $code Reported exit code (negative means unknown)
     * @param float $elapsed Wall-clock duration in seconds
     * @return array{check: Check, result: CheckResult, elapsed: float}
     */
    private function collect(array $handle, int $code, float $elapsed): array
    {
        $status = $code;

        if ($status === self::UNKNOWN_EXIT_CODE) {
            $status = proc_close($handle['proc']);
        } else {
            proc_close($handle['proc']);
        }

        rewind($handle['stdout']);
        $out = (string) stream_get_contents($handle['stdout']);
        fclose($handle['stdout']);

        rewind($handle['stderr']);
        $err = (string) stream_get_contents($handle['stderr']);
        fclose($handle['stderr']);

        return [
            'check' => $handle['check'],
            'result' => new CheckResult($status, "{$out}{$err}", $elapsed),
            'elapsed' => $elapsed,
        ];
    }
}
