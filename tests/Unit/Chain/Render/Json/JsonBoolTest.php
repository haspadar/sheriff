<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Render\Json\JsonBool;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonBoolTest extends TestCase
{
    #[Test]
    public function rendersTrueAsLiteralTrue(): void
    {
        self::assertSame(
            'true',
            (new JsonBool(new BoolValue(true)))->rendered(),
            'JsonBool must render true as the json literal true',
        );
    }

    #[Test]
    public function rendersFalseAsLiteralFalse(): void
    {
        self::assertSame(
            'false',
            (new JsonBool(new BoolValue(false)))->rendered(),
            'JsonBool must render false as the json literal false',
        );
    }
}
