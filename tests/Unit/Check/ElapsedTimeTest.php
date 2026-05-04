<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ElapsedTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ElapsedTimeTest extends TestCase
{
    #[Test]
    public function formatsSecondsUnderOneMinute(): void
    {
        self::assertSame(
            '5.0s',
            (new ElapsedTime(5.0))->formatted(),
            'ElapsedTime must format seconds under 60 as Xs',
        );
    }

    #[Test]
    public function formatsSubSecondValue(): void
    {
        self::assertSame(
            '0.3s',
            (new ElapsedTime(0.25))->formatted(),
            'ElapsedTime must round sub-second values to one decimal',
        );
    }

    #[Test]
    public function formatsExactlyOneMinute(): void
    {
        self::assertSame(
            '1m00s',
            (new ElapsedTime(60.0))->formatted(),
            'ElapsedTime must format 60 seconds as 1m00s',
        );
    }

    #[Test]
    public function formatsMinutesAndSeconds(): void
    {
        self::assertSame(
            '2m30s',
            (new ElapsedTime(150.0))->formatted(),
            'ElapsedTime must format 150 seconds as 2m30s',
        );
    }

    #[Test]
    public function formatsZeroSeconds(): void
    {
        self::assertSame(
            '0.0s',
            (new ElapsedTime(0.0))->formatted(),
            'ElapsedTime must format zero as 0.0s',
        );
    }

    #[Test]
    public function staysInSecondsWhenRoundedBelowSixty(): void
    {
        self::assertSame(
            '59.9s',
            (new ElapsedTime(59.94))->formatted(),
            'ElapsedTime must stay in seconds format when rounding keeps value below 60',
        );
    }

    #[Test]
    public function switchesToMinutesWhenRoundedToSixty(): void
    {
        self::assertSame(
            '1m00s',
            (new ElapsedTime(59.95))->formatted(),
            'ElapsedTime must use minutes format when rounding pushes value to 60',
        );
    }
}
