<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Formula\Action;

use Haspadar\Sheriff\Formula\Action\ShellQuoteAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ShellQuoteActionTest extends TestCase
{
    /** @return iterable<string, array{list<string>}> */
    public static function payloads(): iterable
    {
        yield 'plain values' => [['red', 'green', 'blue']];
        yield 'values with spaces' => [['hello world', 'foo bar baz']];
        yield 'values with single quotes' => [["it's", "'quoted'"]];
        yield 'values with dollar signs' => [['$HOME', '${PATH}']];
        yield 'values with backticks' => [['`whoami`']];
        yield 'values with backslashes' => [['a\\b', 'c\\\\d']];
        yield 'mixed metacharacters' => [["a b", "c'd", '$e`f', 'g\\h']];
        yield 'empty string alongside values' => [['a', '', 'b']];
    }

    #[Test]
    #[DataProvider('payloads')]
    public function survivesShellReparse(array $originals): void
    {
        $quoted = (new ShellQuoteAction())
            ->transformed(new ListArgs($originals))
            ->values();

        $joined = implode(' ', array_map(static fn(int|float|string|bool $v): string => (string) $v, $quoted));

        self::assertSame(
            $originals,
            self::reparsedByShell($joined),
            'Joined quoted tokens must re-parse via shell back to the original values',
        );
    }

    /**
     * @return list<string>
     */
    private static function reparsedByShell(string $joinedQuoted): array
    {
        $script = "printf '%s\\n' " . $joinedQuoted;

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(['/bin/sh', '-c', $script], $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to launch /bin/sh');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException(sprintf('Shell exited with %d: %s', $exitCode, (string) $stderr));
        }

        $output = (string) $stdout;

        if ($output === '') {
            return [];
        }

        $lines = explode("\n", rtrim($output, "\n"));

        return array_values($lines);
    }
}
