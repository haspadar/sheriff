<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Integration\Cli;

use Haspadar\Piqule\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function fclose;
use function proc_close;
use function proc_open;
use function stream_get_contents;

final class SheriffAliasTest extends TestCase
{
    private const string SCRIPT = __DIR__ . '/../../../bin/sheriff';

    #[Test]
    public function showsSheriffUsageWhenCommandIsUnknown(): void
    {
        self::assertSame(
            "Usage: sheriff [sync|check|fix|agent-rules-install]\n",
            $this->stdout([self::SCRIPT, 'unknown']),
            'sheriff alias must identify itself in usage output',
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
                'bin/piqule-check',
                'bin/piqule-fix',
                'bin/piqule-sync',
                'bin/piqule-pin',
                'bin/piqule-verify',
                'bin/piqule-tokens-check',
                'bin/piqule-agent-rules-check',
                'bin/piqule-agent-rules-install',
                'bin/piqule',
            ],
            $this->composerBin(),
            'Composer install must expose helper binaries used by CLI dispatch',
        );
    }

    #[Test]
    public function verifiesTemplatesWhenSheriffHelpersRunFromComposerBin(): void
    {
        $folder = (new TempFolder())->withFile('.piqule/.keep', '');

        try {
            mkdir($folder->path() . '/vendor/bin', 0o755, true);
            symlink(self::SCRIPT, $folder->path() . '/vendor/bin/sheriff');
            symlink(__DIR__ . '/../../../bin/piqule', $folder->path() . '/vendor/bin/piqule');
            symlink(__DIR__ . '/../../../bin/sheriff-pin', $folder->path() . '/vendor/bin/sheriff-pin');
            symlink(__DIR__ . '/../../../bin/piqule-pin', $folder->path() . '/vendor/bin/piqule-pin');
            symlink(__DIR__ . '/../../../bin/sheriff-verify', $folder->path() . '/vendor/bin/sheriff-verify');
            symlink(__DIR__ . '/../../../bin/piqule-verify', $folder->path() . '/vendor/bin/piqule-verify');

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

    /**
     * @param list<string> $command
     */
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

    /**
     * @return list<string>
     */
    private function composerBin(): array
    {
        $json = json_decode((string) file_get_contents(__DIR__ . '/../../../composer.json'), true);

        /** @var list<string> $bin */
        $bin = is_array($json) && isset($json['bin']) && is_array($json['bin']) ? $json['bin'] : [];

        return $bin;
    }
}
