<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Plain;

use Haspadar\Sheriff\Chain\Plain\IntText;
use Haspadar\Sheriff\Settings\Value\IntValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IntTextTest extends TestCase
{
    #[Test]
    public function rendersPositiveIntegerAsDecimalString(): void
    {
        self::assertSame(
            '8',
            (new IntText(new IntValue(8)))->rendered(),
            'IntText must render a positive integer as its decimal string',
        );
    }

    #[Test]
    public function rendersNegativeIntegerWithLeadingMinus(): void
    {
        self::assertSame(
            '-3',
            (new IntText(new IntValue(-3)))->rendered(),
            'IntText must keep the leading minus sign for negative integers',
        );
    }

    #[Test]
    public function rendersZeroAsLiteralZero(): void
    {
        self::assertSame(
            '0',
            (new IntText(new IntValue(0)))->rendered(),
            'IntText must render zero as the bare digit "0"',
        );
    }

    #[Test]
    public function rendersIntMaxBoundaryWithoutOverflow(): void
    {
        self::assertSame(
            (string) PHP_INT_MAX,
            (new IntText(new IntValue(PHP_INT_MAX)))->rendered(),
            'IntText must round-trip PHP_INT_MAX through string casting without loss',
        );
    }

    #[Test]
    public function rendersIntMinBoundaryWithoutOverflow(): void
    {
        self::assertSame(
            (string) PHP_INT_MIN,
            (new IntText(new IntValue(PHP_INT_MIN)))->rendered(),
            'IntText must round-trip PHP_INT_MIN through string casting without loss',
        );
    }
}
