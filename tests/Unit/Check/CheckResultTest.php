<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\CheckResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckResultTest extends TestCase
{
    #[Test]
    public function passedWhenStatusIsZero(): void
    {
        self::assertTrue(
            (new CheckResult(0, 'ok', 1.0))->passed(),
            'CheckResult must report passed when status is 0',
        );
    }

    #[Test]
    public function failedWhenStatusIsNonZero(): void
    {
        self::assertFalse(
            (new CheckResult(1, 'fail', 2.0))->passed(),
            'CheckResult must report failed when status is non-zero',
        );
    }

    #[Test]
    public function returnsOutput(): void
    {
        self::assertSame(
            'some output',
            (new CheckResult(0, 'some output', 0.5))->output(),
            'CheckResult must return the output string',
        );
    }

    #[Test]
    public function returnsElapsed(): void
    {
        self::assertSame(
            3.14,
            (new CheckResult(0, '', 3.14))->elapsed(),
            'CheckResult must return the elapsed time in seconds',
        );
    }
}
