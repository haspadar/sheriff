<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Render\Json\JsonInt;
use Haspadar\Sheriff\Settings\Value\IntValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonIntTest extends TestCase
{
    #[Test]
    public function rendersPositiveIntegerAsBareNumber(): void
    {
        self::assertSame(
            '80',
            (new JsonInt(new IntValue(80)))->rendered(),
            'JsonInt must render the integer payload as a bare json number',
        );
    }

    #[Test]
    public function rendersNegativeIntegerAsBareNumber(): void
    {
        self::assertSame(
            '-3',
            (new JsonInt(new IntValue(-3)))->rendered(),
            'JsonInt must keep the sign on negative integers',
        );
    }
}
