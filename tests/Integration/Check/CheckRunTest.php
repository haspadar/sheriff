<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Check;

use Haspadar\Sheriff\Check\CheckRun;
use Haspadar\Sheriff\Tests\Fake\Check\FakeCheck;
use Haspadar\Sheriff\Tests\Fake\Check\FakeCliOption;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckRunTest extends TestCase
{
    #[Test]
    public function returnsPassedResultWhenCommandSucceeds(): void
    {
        $folder = (new TempFolder())->withFile('command.sh', 'echo ok');

        try {
            $result = (new CheckRun(
                new FakeCheck('test', $folder->path() . '/command.sh'),
                new FakeCliOption(false),
            ))->result();

            self::assertTrue(
                $result->passed(),
                'CheckRun must return passed result when command exits with 0',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function returnsFailedResultWhenCommandFails(): void
    {
        $folder = (new TempFolder())->withFile('command.sh', 'exit 1');

        try {
            $result = (new CheckRun(
                new FakeCheck('test', $folder->path() . '/command.sh'),
                new FakeCliOption(false),
            ))->result();

            self::assertFalse(
                $result->passed(),
                'CheckRun must return failed result when command exits with non-zero',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function capturesCommandOutputInQuietMode(): void
    {
        $folder = (new TempFolder())->withFile('command.sh', 'echo "hello world"');

        try {
            $result = (new CheckRun(
                new FakeCheck('test', $folder->path() . '/command.sh'),
                new FakeCliOption(false),
            ))->result();

            self::assertSame(
                'hello world',
                $result->output(),
                'CheckRun must capture command output in quiet mode',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function returnsEmptyOutputInVerboseMode(): void
    {
        $folder = (new TempFolder())->withFile('command.sh', 'echo "hello"');

        try {
            $result = (new CheckRun(
                new FakeCheck('test', $folder->path() . '/command.sh'),
                new FakeCliOption(true),
            ))->result();

            self::assertSame(
                '',
                $result->output(),
                'CheckRun must return empty output in verbose mode (passthru writes directly)',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function measuresElapsedTime(): void
    {
        $folder = (new TempFolder())->withFile('command.sh', 'sleep 0.05');

        try {
            $result = (new CheckRun(
                new FakeCheck('test', $folder->path() . '/command.sh'),
                new FakeCliOption(false),
            ))->result();

            self::assertGreaterThan(
                0.0,
                $result->elapsed(),
                'CheckRun must measure positive elapsed time',
            );
        } finally {
            $folder->close();
        }
    }
}
