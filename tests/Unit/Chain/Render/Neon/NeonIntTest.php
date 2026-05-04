<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonInt;
use Haspadar\Sheriff\Settings\Value\IntValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeonIntTest extends TestCase
{
    #[Test]
    public function rendersIntegerAsBareLiteral(): void
    {
        self::assertSame(
            '8',
            (new NeonInt(new IntValue(8)))->rendered(),
            'NeonInt must render the integer payload as a bare neon literal',
        );
    }

    #[Test]
    public function preservesNegativeSign(): void
    {
        self::assertSame(
            '-5',
            (new NeonInt(new IntValue(-5)))->rendered(),
            'NeonInt must preserve the negative sign in the rendered literal',
        );
    }
}
