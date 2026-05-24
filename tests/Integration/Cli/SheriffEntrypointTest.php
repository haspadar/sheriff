<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Cli;

use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function fclose;
use function proc_close;
use function proc_open;
use function stream_get_contents;

final class SheriffEntrypointTest extends TestCase
{
    private const string SCRIPT = __DIR__ . '/../../../bin/sheriff';

    #[Test]
    public function showsSheriffUsageWhenCommandIsUnknown(): void
    {
        self::assertSame(
            "Usage: sheriff [sync|check|fix|agent-rules-install]\n",
            $this->stdout([self::SCRIPT, 'unknown']),
            'sheriff entrypoint must identify itself in usage output',
        );
    }

    #[Test]
    public function declaresHelperBinariesForComposerInstall(): void
    {
        self::assertSame(
            [
                'bin/sheriff',
                'bin/sheriff-check',
                'bin/sheriff-fix',
                'bin/sheriff-sync',
                'bin/sheriff-pin',
                'bin/sheriff-verify',
                'bin/sheriff-tokens-check',
                'bin/sheriff-agent-rules-check',
                'bin/sheriff-agent-rules-install',
            ],
            $this->composerBin(),
            'Composer install must expose helper binaries used by CLI dispatch',
        );
    }

    #[Test]
    public function verifiesTemplatesWhenSheriffHelpersRunFromComposerBin(): void
    {
        $folder = (new TempFolder())->withFile('.sheriff/.keep', '');

        try {
            mkdir($folder->path() . '/vendor/bin', 0o755, true);
            symlink(self::SCRIPT, $folder->path() . '/vendor/bin/sheriff');
            symlink(__DIR__ . '/../../../bin/sheriff-pin', $folder->path() . '/vendor/bin/sheriff-pin');
            symlink(__DIR__ . '/../../../bin/sheriff-verify', $folder->path() . '/vendor/bin/sheriff-verify');

            $this->stdout([$folder->path() . '/vendor/bin/sheriff-pin'], $folder->path());

            self::assertStringContainsString(
                'Templates are up to date',
                $this->stdout([$folder->path() . '/vendor/bin/sheriff-verify'], $folder->path()),
                'sheriff helpers must resolve package templates when run from Composer bin',
            );
        } finally {
            $folder->close();
        }
    }

    /** @param list<string> $command */
    private function stdout(array $command, ?string $cwd = null): string
    {
        $proc = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $cwd,
        );

        if (!is_resource($proc)) {
            self::fail('Failed to start sheriff subprocess');
        }

        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        return $stdout;
    }

    /** @return list<string> */
    private function composerBin(): array
    {
        $json = json_decode((string) file_get_contents(__DIR__ . '/../../../composer.json'), true);

        /** @var list<string> $bin */
        $bin = is_array($json) && array_key_exists('bin', $json) && is_array($json['bin'])
            ? $json['bin']
            : [];

        return $bin;
    }
}
