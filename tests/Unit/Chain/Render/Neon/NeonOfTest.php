<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonBool;
use Haspadar\Sheriff\Chain\Render\Neon\NeonFloat;
use Haspadar\Sheriff\Chain\Render\Neon\NeonInt;
use Haspadar\Sheriff\Chain\Render\Neon\NeonList;
use Haspadar\Sheriff\Chain\Render\Neon\NeonOf;
use Haspadar\Sheriff\Chain\Render\Neon\NeonString;
use Haspadar\Sheriff\Chain\Render\Neon\NeonTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class NeonOfTest extends TestCase
{
    #[Test]
    public function pairsBoolValueWithNeonBool(): void
    {
        self::assertInstanceOf(
            NeonBool::class,
            (new NeonOf(new BoolValue(true)))->renderer(),
            'NeonOf must pair BoolValue with the NeonBool renderer',
        );
    }

    #[Test]
    public function pairsIntValueWithNeonInt(): void
    {
        self::assertInstanceOf(
            NeonInt::class,
            (new NeonOf(new IntValue(8)))->renderer(),
            'NeonOf must pair IntValue with the NeonInt renderer',
        );
    }

    #[Test]
    public function pairsFloatValueWithNeonFloat(): void
    {
        self::assertInstanceOf(
            NeonFloat::class,
            (new NeonOf(new FloatValue(0.5)))->renderer(),
            'NeonOf must pair FloatValue with the NeonFloat renderer',
        );
    }

    #[Test]
    public function pairsStringValueWithNeonString(): void
    {
        self::assertInstanceOf(
            NeonString::class,
            (new NeonOf(new StringValue('foo')))->renderer(),
            'NeonOf must pair StringValue with the NeonString renderer',
        );
    }

    #[Test]
    public function pairsListValueWithNeonList(): void
    {
        self::assertInstanceOf(
            NeonList::class,
            (new NeonOf(new ListValue([])))->renderer(),
            'NeonOf must pair ListValue with the NeonList renderer',
        );
    }

    #[Test]
    public function pairsTreeValueWithNeonTree(): void
    {
        self::assertInstanceOf(
            NeonTree::class,
            (new NeonOf(new TreeValue([])))->renderer(),
            'NeonOf must pair TreeValue with the NeonTree renderer',
        );
    }

    #[Test]
    public function rejectsValueSubtypeWithoutMatchingRenderer(): void
    {
        $this->expectException(TypeError::class);

        $unknown = new class () implements Value {
        };

        (new NeonOf($unknown))->renderer();
    }
}
