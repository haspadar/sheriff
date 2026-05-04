<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Plain;

use Haspadar\Sheriff\Chain\Plain\BoolText;
use Haspadar\Sheriff\Chain\Plain\FloatText;
use Haspadar\Sheriff\Chain\Plain\IntText;
use Haspadar\Sheriff\Chain\Plain\ListText;
use Haspadar\Sheriff\Chain\Plain\StringText;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListTextTest extends TestCase
{
    #[Test]
    public function wrapsStringChildrenIntoStringTextParts(): void
    {
        self::assertContainsOnlyInstancesOf(
            StringText::class,
            (new ListText(new ListValue([
                new StringValue('src'),
                new StringValue('tests'),
            ])))->parts(),
            'ListText must wrap StringValue children into StringText parts',
        );
    }

    #[Test]
    public function preservesChildOrderInRenderedParts(): void
    {
        self::assertSame(
            ['src', 'tests', 'docs'],
            array_map(
                fn (object $part): string => $part->rendered(),
                (new ListText(new ListValue([
                    new StringValue('src'),
                    new StringValue('tests'),
                    new StringValue('docs'),
                ])))->parts(),
            ),
            'ListText must preserve the order of children when exposing parts',
        );
    }

    #[Test]
    public function dispatchesEachScalarChildToItsMatchingPlainOp(): void
    {
        $parts = (new ListText(new ListValue([
            new BoolValue(true),
            new IntValue(8),
            new FloatValue(0.5),
        ])))->parts();

        self::assertSame(
            [BoolText::class, IntText::class, FloatText::class],
            [$parts[0]::class, $parts[1]::class, $parts[2]::class],
            'ListText must dispatch each scalar child to its matching Plain op type',
        );
    }

    #[Test]
    public function returnsEmptyPartsForEmptyListValue(): void
    {
        self::assertSame(
            [],
            (new ListText(new ListValue([])))->parts(),
            'ListText must return no parts when the source list is empty',
        );
    }

    #[Test]
    public function refusesDirectRendering(): void
    {
        $this->expectException(SheriffException::class);

        (new ListText(new ListValue([new StringValue('src')])))->rendered();
    }

    #[Test]
    public function rejectsNestedTreeChildren(): void
    {
        $this->expectException(SheriffException::class);

        (new ListText(new ListValue([new TreeValue([])])))->parts();
    }

    #[Test]
    public function rejectsNestedListChildren(): void
    {
        $this->expectException(SheriffException::class);

        (new ListText(new ListValue([new ListValue([])])))->parts();
    }
}
