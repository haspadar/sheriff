<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Plain;

use Haspadar\Sheriff\Chain\Plain\FloatText;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FloatTextTest extends TestCase
{
    #[Test]
    public function rendersFiniteFloatAsDecimalString(): void
    {
        self::assertSame(
            '0.5',
            (new FloatText(new FloatValue(0.5)))->rendered(),
            'FloatText must render a finite float as its decimal string',
        );
    }

    #[Test]
    public function rejectsInfinity(): void
    {
        $this->expectException(SheriffException::class);

        (new FloatText(new FloatValue(INF)))->rendered();
    }

    #[Test]
    public function rejectsNotANumber(): void
    {
        $this->expectException(SheriffException::class);

        (new FloatText(new FloatValue(NAN)))->rendered();
    }

    #[Test]
    public function rejectsNegativeInfinity(): void
    {
        $this->expectException(SheriffException::class);

        (new FloatText(new FloatValue(-INF)))->rendered();
    }

    #[Test]
    public function rendersNegativeFiniteFloatWithLeadingMinus(): void
    {
        self::assertSame(
            '-0.5',
            (new FloatText(new FloatValue(-0.5)))->rendered(),
            'FloatText must keep the leading minus sign for negative finite floats',
        );
    }

    #[Test]
    public function rendersWholeFloatWithoutDecimalPoint(): void
    {
        self::assertSame(
            '1',
            (new FloatText(new FloatValue(1.0)))->rendered(),
            'FloatText pins PHP default behavior of rendering 1.0 as "1" via string cast',
        );
    }
}
