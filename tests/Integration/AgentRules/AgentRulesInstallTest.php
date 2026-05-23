<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\AgentRules;

use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function proc_close;
use function proc_open;

final class AgentRulesInstallTest extends TestCase
{
    private const string SCRIPT = __DIR__ . '/../../../bin/sheriff-agent-rules-install';

    private const string MARKER = '<!-- sheriff:begin -->';

    #[Test]
    public function leavesDirectoryUntouchedWhenNoAgentFilePresent(): void
    {
        $folder = new TempFolder();

        try {
            $this->runScript($folder->path());

            self::assertFileDoesNotExist(
                $folder->path() . '/CLAUDE.md',
                'CLAUDE.md must not be created when the user has no agent file',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function appendsSectionToClaudeWhenMarkerAbsent(): void
    {
        $folder = (new TempFolder())->withFile('CLAUDE.md', "# Project rules\nKeep tests green.\n");

        try {
            $this->runScript($folder->path());

            self::assertStringContainsString(
                self::MARKER,
                (string) file_get_contents($folder->path() . '/CLAUDE.md'),
                'sheriff section must be appended to CLAUDE.md when marker is missing',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function preservesUserContentWhenAppendingToClaude(): void
    {
        $folder = (new TempFolder())->withFile('CLAUDE.md', "# Project rules\nKeep tests green.\n");

        try {
            $this->runScript($folder->path());

            self::assertStringContainsString(
                '# Project rules',
                (string) file_get_contents($folder->path() . '/CLAUDE.md'),
                'existing user content must be preserved when section is appended',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function leavesClaudeUnchangedWhenMarkerAlreadyPresent(): void
    {
        $original = "# Project rules\n" . self::MARKER . "\nstale content\n<!-- sheriff:end -->\n";
        $folder = (new TempFolder())->withFile('CLAUDE.md', $original);

        try {
            $this->runScript($folder->path());

            self::assertSame(
                $original,
                (string) file_get_contents($folder->path() . '/CLAUDE.md'),
                'CLAUDE.md must stay untouched when sheriff marker is already present',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function appendsSectionToAgentsWhenMarkerAbsent(): void
    {
        $folder = (new TempFolder())->withFile('AGENTS.md', "# Agents\n");

        try {
            $this->runScript($folder->path());

            self::assertStringContainsString(
                self::MARKER,
                (string) file_get_contents($folder->path() . '/AGENTS.md'),
                'sheriff section must be appended to AGENTS.md when marker is missing',
            );
        } finally {
            $folder->close();
        }
    }

    private function runScript(string $cwd): void
    {
        $proc = proc_open(
            [PHP_BINARY, self::SCRIPT],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $cwd,
        );

        if (!is_resource($proc)) {
            self::fail('Failed to start sheriff-agent-rules-install subprocess');
        }

        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        if ($exitCode !== 0) {
            self::fail(sprintf(
                'sheriff-agent-rules-install exited with code %d: %s',
                $exitCode,
                $stderr,
            ));
        }
    }
}
