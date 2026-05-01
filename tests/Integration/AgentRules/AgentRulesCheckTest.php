<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Integration\AgentRules;

use Haspadar\Piqule\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function proc_close;
use function proc_open;

final class AgentRulesCheckTest extends TestCase
{
    private const string SCRIPT = __DIR__ . '/../../../bin/piqule-agent-rules-check';

    private const string MARKER = '<!-- piqule:begin -->';

    #[Test]
    public function staysSilentWhenNoAgentFilePresent(): void
    {
        $folder = new TempFolder();

        try {
            self::assertSame(
                '',
                $this->runStdout($folder->path()),
                'no output must be produced when no agent files exist',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function hintsWhenClaudeExistsWithoutMarker(): void
    {
        $folder = (new TempFolder())->withFile('CLAUDE.md', "# Project rules\n");

        try {
            self::assertStringContainsString(
                'snob agent-rules-install',
                $this->runStdout($folder->path()),
                'hint to run agent-rules-install must be printed when CLAUDE.md has no piqule marker',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function namesFileInHintWhenMarkerMissing(): void
    {
        $folder = (new TempFolder())->withFile('AGENTS.md', "# Agents\n");

        try {
            self::assertStringContainsString(
                'AGENTS.md',
                $this->runStdout($folder->path()),
                'hint must reference the specific agent file missing the marker',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function staysSilentWhenMarkerAlreadyPresent(): void
    {
        $content = "# Project rules\n" . self::MARKER . "\n<!-- piqule:end -->\n";
        $folder = (new TempFolder())->withFile('CLAUDE.md', $content);

        try {
            self::assertSame(
                '',
                $this->runStdout($folder->path()),
                'no hint must be printed when piqule marker is already present',
            );
        } finally {
            $folder->close();
        }
    }

    private function runStdout(string $cwd): string
    {
        $proc = proc_open(
            [PHP_BINARY, self::SCRIPT],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $cwd,
        );

        if (!is_resource($proc)) {
            self::fail('Failed to start piqule-agent-rules-check subprocess');
        }

        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        return preg_replace('/\033\[[0-9;]*m/', '', $stdout) ?? $stdout;
    }
}
