<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\AppendedTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AppendedTreeTest extends TestCase
{
    #[Test]
    public function copiesBaseEntriesWhenExtraIsEmpty(): void
    {
        $base = new TreeValue(['a' => new IntValue(1)]);

        self::assertEquals(
            $base,
            (new AppendedTree($base, new TreeValue([])))->value(),
            'AppendedTree must return the base tree when extra is empty',
        );
    }

    #[Test]
    public function copiesExtraEntriesWhenBaseIsEmpty(): void
    {
        $extra = new TreeValue(['a' => new IntValue(1)]);

        self::assertEquals(
            $extra,
            (new AppendedTree(new TreeValue([]), $extra))->value(),
            'AppendedTree must return the extra tree when base is empty',
        );
    }

    #[Test]
    public function recursesIntoNestedTrees(): void
    {
        self::assertEquals(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'kept' => new BoolValue(true),
                    'added' => new IntValue(42),
                ]),
            ]),
            (new AppendedTree(
                new TreeValue([
                    'haspadar' => new TreeValue(['kept' => new BoolValue(true)]),
                ]),
                new TreeValue([
                    'haspadar' => new TreeValue(['added' => new IntValue(42)]),
                ]),
            ))->value(),
            'AppendedTree must merge matching nested trees instead of replacing them',
        );
    }

    #[Test]
    public function concatenatesMatchingListLeaves(): void
    {
        self::assertEquals(
            new TreeValue([
                'excludedClasses' => new ListValue([
                    new StringValue('A'),
                    new StringValue('B'),
                ]),
            ]),
            (new AppendedTree(
                new TreeValue([
                    'excludedClasses' => new ListValue([new StringValue('A')]),
                ]),
                new TreeValue([
                    'excludedClasses' => new ListValue([new StringValue('B')]),
                ]),
            ))->value(),
            'AppendedTree must concatenate matching list leaves in append order',
        );
    }

    #[Test]
    public function rejectsScalarCollision(): void
    {
        $this->expectException(SheriffException::class);

        (new AppendedTree(
            new TreeValue(['flag' => new BoolValue(false)]),
            new TreeValue(['flag' => new BoolValue(true)]),
        ))->value();
    }

    #[Test]
    public function rejectsTreeAndListCollision(): void
    {
        $this->expectException(SheriffException::class);

        (new AppendedTree(
            new TreeValue(['x' => new TreeValue([])]),
            new TreeValue(['x' => new ListValue([])]),
        ))->value();
    }

    #[Test]
    public function reportsFullDottedPathOnNestedCollision(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('AppendedTree cannot merge "haspadar.afferentCoupling.flag"');

        (new AppendedTree(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'flag' => new BoolValue(false),
                    ]),
                ]),
            ]),
            new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'flag' => new BoolValue(true),
                    ]),
                ]),
            ]),
        ))->value();
    }
}
