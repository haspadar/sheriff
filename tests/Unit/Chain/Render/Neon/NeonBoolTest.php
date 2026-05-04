<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonBool;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeonBoolTest extends TestCase
{
    #[Test]
    public function rendersTrueAsLiteralTrue(): void
    {
        self::assertSame(
            'true',
            (new NeonBool(new BoolValue(true)))->rendered(),
            'NeonBool must render true as the neon literal "true"',
        );
    }

    #[Test]
    public function rendersFalseAsLiteralFalse(): void
    {
        self::assertSame(
            'false',
            (new NeonBool(new BoolValue(false)))->rendered(),
            'NeonBool must render false as the neon literal "false"',
        );
    }
}
