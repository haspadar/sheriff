<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\RequestedCheck;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestedCheckTest extends TestCase
{
    #[Test]
    public function returnsCheckNameFromArgv(): void
    {
        self::assertSame(
            'phpstan',
            (new RequestedCheck(['check', 'phpstan']))->name(),
            'RequestedCheck must return the second positional argument',
        );
    }

    #[Test]
    public function returnsEmptyWhenNoCheckSpecified(): void
    {
        self::assertSame(
            '',
            (new RequestedCheck(['check']))->name(),
            'RequestedCheck must return empty string when no check name given',
        );
    }

    #[Test]
    public function skipsVerboseFlag(): void
    {
        self::assertSame(
            'phpunit',
            (new RequestedCheck(['check', '-v', 'phpunit']))->name(),
            'RequestedCheck must skip -v flag when extracting name',
        );
    }

    #[Test]
    public function skipsLongVerboseFlag(): void
    {
        self::assertSame(
            'psalm',
            (new RequestedCheck(['check', '--verbose', 'psalm']))->name(),
            'RequestedCheck must skip --verbose flag when extracting name',
        );
    }

    #[Test]
    public function skipsParallelFlag(): void
    {
        self::assertSame(
            'phpcs',
            (new RequestedCheck(['check', '-p', 'phpcs']))->name(),
            'RequestedCheck must skip -p flag when extracting name',
        );
    }

    #[Test]
    public function skipsMultipleFlags(): void
    {
        self::assertSame(
            'infection',
            (new RequestedCheck(['check', '-v', '-p', 'infection']))->name(),
            'RequestedCheck must skip multiple flags when extracting name',
        );
    }

    #[Test]
    public function returnsEmptyWhenOnlyFlagsPresent(): void
    {
        self::assertSame(
            '',
            (new RequestedCheck(['-v', '--parallel']))->name(),
            'RequestedCheck must return empty when argv contains only flags',
        );
    }

    #[Test]
    public function skipsFullFlag(): void
    {
        self::assertSame(
            'phpstan',
            (new RequestedCheck(['check', '-f', 'phpstan']))->name(),
            'RequestedCheck must skip -f flag when extracting name',
        );
    }

    #[Test]
    public function skipsNoParallelFlag(): void
    {
        self::assertSame(
            'phpunit',
            (new RequestedCheck(['check', '-P', 'phpunit']))->name(),
            'RequestedCheck must skip -P (no-parallel) flag when extracting name',
        );
    }
}
