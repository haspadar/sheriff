<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonFloat;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class NeonFloatTest extends TestCase
{
    #[Test]
    public function rendersFloatAsBareLiteral(): void
    {
        self::assertSame(
            '0.5',
            (new NeonFloat(new FloatValue(0.5)))->rendered(),
            'NeonFloat must render the float payload as a bare neon literal',
        );
    }

    #[Test]
    public function preservesDecimalPointForWholeNumberFloats(): void
    {
        self::assertSame(
            '1.0',
            (new NeonFloat(new FloatValue(1.0)))->rendered(),
            'NeonFloat must keep the decimal point so a whole-number float stays distinguishable from an integer',
        );
    }

    #[Test]
    public function rejectsInfinityPayload(): void
    {
        $this->expectException(UnexpectedValueException::class);

        (new NeonFloat(new FloatValue(INF)))->rendered();
    }

    #[Test]
    public function rejectsNanPayload(): void
    {
        $this->expectException(UnexpectedValueException::class);

        (new NeonFloat(new FloatValue(NAN)))->rendered();
    }
}
