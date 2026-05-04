<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\MergedTree;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MergedTreeTest extends TestCase
{
    #[Test]
    public function copiesBaseEntriesWhenOverrideIsEmpty(): void
    {
        $base = new TreeValue(['a' => new IntValue(1)]);

        self::assertEquals(
            $base,
            (new MergedTree($base, new TreeValue([])))->value(),
            'MergedTree must return the base tree when the override is empty',
        );
    }

    #[Test]
    public function copiesOverrideEntriesWhenBaseIsEmpty(): void
    {
        $override = new TreeValue(['a' => new IntValue(1)]);

        self::assertEquals(
            $override,
            (new MergedTree(new TreeValue([]), $override))->value(),
            'MergedTree must return the override tree when the base is empty',
        );
    }

    #[Test]
    public function addsKeyAbsentInBase(): void
    {
        self::assertEquals(
            new TreeValue([
                'kept' => new IntValue(1),
                'added' => new BoolValue(true),
            ]),
            (new MergedTree(
                new TreeValue(['kept' => new IntValue(1)]),
                new TreeValue(['added' => new BoolValue(true)]),
            ))->value(),
            'MergedTree must add a key from the override when it is absent in the base',
        );
    }

    #[Test]
    public function replacesLeafAtTopLevel(): void
    {
        self::assertEquals(
            new TreeValue(['flag' => new BoolValue(true)]),
            (new MergedTree(
                new TreeValue(['flag' => new BoolValue(false)]),
                new TreeValue(['flag' => new BoolValue(true)]),
            ))->value(),
            'MergedTree must replace a top-level leaf when both sides hold non-tree values',
        );
    }

    #[Test]
    public function replacesBaseBranchWhenOverrideIsNotTree(): void
    {
        self::assertEquals(
            new TreeValue(['tag' => new StringValue('new')]),
            (new MergedTree(
                new TreeValue(['tag' => new TreeValue(['name' => new StringValue('old')])]),
                new TreeValue(['tag' => new StringValue('new')]),
            ))->value(),
            'MergedTree must replace the base branch when the override leaf is not a tree',
        );
    }

    #[Test]
    public function replacesScalarBaseWithTreeOverride(): void
    {
        self::assertEquals(
            new TreeValue(['tag' => new TreeValue(['name' => new StringValue('new')])]),
            (new MergedTree(
                new TreeValue(['tag' => new StringValue('old')]),
                new TreeValue(['tag' => new TreeValue(['name' => new StringValue('new')])]),
            ))->value(),
            'MergedTree must replace a scalar base with the override tree at a shared key',
        );
    }

    #[Test]
    public function recursesIntoNestedTreesAtSharedKeys(): void
    {
        $base = new TreeValue([
            'outer' => new TreeValue([
                'kept' => new IntValue(1),
                'replaced' => new BoolValue(false),
            ]),
        ]);
        $override = new TreeValue([
            'outer' => new TreeValue(['replaced' => new BoolValue(true)]),
        ]);

        self::assertEquals(
            new TreeValue([
                'outer' => new TreeValue([
                    'kept' => new IntValue(1),
                    'replaced' => new BoolValue(true),
                ]),
            ]),
            (new MergedTree($base, $override))->value(),
            'MergedTree must recurse into nested trees at shared keys and preserve siblings',
        );
    }
}
