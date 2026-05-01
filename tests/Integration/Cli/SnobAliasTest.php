<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Integration\Cli;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function fclose;
use function proc_close;
use function proc_open;
use function stream_get_contents;

final class SnobAliasTest extends TestCase
{
    private const string SCRIPT = __DIR__ . '/../../../bin/snob';

    #[Test]
    public function showsSnobUsageWhenCommandIsUnknown(): void
    {
        self::assertSame(
            "Usage: snob [sync|check|fix|agent-rules-install]\n",
            $this->stdout(),
            'snob alias must identify itself in usage output',
        );
    }

    private function stdout(): string
    {
        $proc = proc_open(
            [self::SCRIPT, 'unknown'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );

        if (!is_resource($proc)) {
            self::fail('Failed to start snob subprocess');
        }

        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        return $stdout;
    }
}
