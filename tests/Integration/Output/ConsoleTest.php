<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Output;

use Haspadar\Sheriff\Tests\Fixture\ConsoleProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConsoleTest extends TestCase
{
    #[Test]
    public function writesYellowTextToStdoutOnInfo(): void
    {
        self::assertSame(
            "\033[33mhello\033[0m\n",
            (new ConsoleProcess('info', 'hello'))->stdout(),
            'info() must write yellow ANSI text to stdout',
        );
    }

    #[Test]
    public function writesGreenTextToStdoutOnSuccess(): void
    {
        self::assertSame(
            "\033[32mdone\033[0m\n",
            (new ConsoleProcess('success', 'done'))->stdout(),
            'success() must write green ANSI text to stdout',
        );
    }

    #[Test]
    public function writesRedTextToStderrOnError(): void
    {
        self::assertSame(
            "\033[31mfail\033[0m\n",
            (new ConsoleProcess('error', 'fail'))->stderr(),
            'error() must write red ANSI text to stderr',
        );
    }

    #[Test]
    public function writesGrayTextToStdoutOnMuted(): void
    {
        self::assertSame(
            "\033[90mskip\033[0m\n",
            (new ConsoleProcess('muted', 'skip'))->stdout(),
            'muted() must write gray ANSI text to stdout',
        );
    }

    #[Test]
    public function writesNothingToStderrOnInfo(): void
    {
        self::assertSame(
            '',
            (new ConsoleProcess('info', 'hello'))->stderr(),
            'info() must not write to stderr',
        );
    }

    #[Test]
    public function writesNothingToStderrOnSuccess(): void
    {
        self::assertSame(
            '',
            (new ConsoleProcess('success', 'done'))->stderr(),
            'success() must not write to stderr',
        );
    }

    #[Test]
    public function writesNothingToStdoutOnError(): void
    {
        self::assertSame(
            '',
            (new ConsoleProcess('error', 'fail'))->stdout(),
            'error() must not write to stdout',
        );
    }

    #[Test]
    public function writesNothingToStderrOnMuted(): void
    {
        self::assertSame(
            '',
            (new ConsoleProcess('muted', 'skip'))->stderr(),
            'muted() must not write to stderr',
        );
    }
}
