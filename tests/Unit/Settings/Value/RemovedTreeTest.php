<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\RemovedTree;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RemovedTreeTest extends TestCase
{
    #[Test]
    public function returnsBaseUnchangedWhenSpecIsEmpty(): void
    {
        $base = new TreeValue(['a' => new IntValue(1)]);

        self::assertEquals(
            $base,
            (new RemovedTree($base, new TreeValue([])))->value(),
            'RemovedTree must return the base tree when the spec is empty',
        );
    }

    #[Test]
    public function ignoresSpecKeysAbsentFromBase(): void
    {
        $base = new TreeValue(['kept' => new IntValue(1)]);

        self::assertEquals(
            $base,
            (new RemovedTree(
                $base,
                new TreeValue(['absent' => new ListValue([])]),
            ))->value(),
            'RemovedTree must keep base entries when the spec targets keys absent from base',
        );
    }

    #[Test]
    public function dropsListEntriesAtMatchingListLeaves(): void
    {
        self::assertEquals(
            new TreeValue([
                'excludedClasses' => new ListValue([new StringValue('\\Kept')]),
            ]),
            (new RemovedTree(
                new TreeValue([
                    'excludedClasses' => new ListValue([
                        new StringValue('\\Kept'),
                        new StringValue('\\Dropped'),
                    ]),
                ]),
                new TreeValue([
                    'excludedClasses' => new ListValue([new StringValue('\\Dropped')]),
                ]),
            ))->value(),
            'RemovedTree must drop spec entries from a base list at the matching key',
        );
    }

    #[Test]
    public function recursesIntoNestedTrees(): void
    {
        self::assertEquals(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'excludedClasses' => new ListValue([new StringValue('\\Kept')]),
                    ]),
                ]),
            ]),
            (new RemovedTree(
                new TreeValue([
                    'haspadar' => new TreeValue([
                        'afferentCoupling' => new TreeValue([
                            'excludedClasses' => new ListValue([
                                new StringValue('\\Kept'),
                                new StringValue('\\Dropped'),
                            ]),
                        ]),
                    ]),
                ]),
                new TreeValue([
                    'haspadar' => new TreeValue([
                        'afferentCoupling' => new TreeValue([
                            'excludedClasses' => new ListValue([new StringValue('\\Dropped')]),
                        ]),
                    ]),
                ]),
            ))->value(),
            'RemovedTree must recurse into matching nested trees and prune list leaves at any depth',
        );
    }

    #[Test]
    public function dropsNamedKeysWhenSpecIsListOfStrings(): void
    {
        self::assertEquals(
            new TreeValue([
                'flags' => new TreeValue(['kept' => new IntValue(1)]),
            ]),
            (new RemovedTree(
                new TreeValue([
                    'flags' => new TreeValue([
                        'kept' => new IntValue(1),
                        'removed' => new BoolValue(true),
                    ]),
                ]),
                new TreeValue([
                    'flags' => new ListValue([new StringValue('removed')]),
                ]),
            ))->value(),
            'RemovedTree must drop named keys from a base tree when the spec at the same key is a list of strings',
        );
    }

    #[Test]
    public function rejectsNonStringEntryInListDropSpec(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('list drop entries must be strings');

        (new RemovedTree(
            new TreeValue([
                'excludedClasses' => new ListValue([new StringValue('\\Foo')]),
            ]),
            new TreeValue([
                'excludedClasses' => new ListValue([new IntValue(42)]),
            ]),
        ))->value();
    }

    #[Test]
    public function rejectsTreeAndScalarCollision(): void
    {
        $this->expectException(SheriffException::class);

        (new RemovedTree(
            new TreeValue(['flag' => new BoolValue(true)]),
            new TreeValue(['flag' => new TreeValue([])]),
        ))->value();
    }

    #[Test]
    public function processesSubsequentSpecKeysAfterEncounteringAnAbsentOne(): void
    {
        self::assertEquals(
            new TreeValue([
                'kept' => new IntValue(1),
                'flags' => new TreeValue([]),
            ]),
            (new RemovedTree(
                new TreeValue([
                    'kept' => new IntValue(1),
                    'flags' => new TreeValue(['removed' => new BoolValue(true)]),
                ]),
                new TreeValue([
                    'absent' => new ListValue([]),
                    'flags' => new ListValue([new StringValue('removed')]),
                ]),
            ))->value(),
            'RemovedTree must keep iterating through spec keys after one targets a key absent from base',
        );
    }

    #[Test]
    public function reindexesListAfterDroppingNonTrailingItems(): void
    {
        self::assertEquals(
            new TreeValue([
                'items' => new ListValue([
                    new StringValue('b'),
                    new StringValue('c'),
                ]),
            ]),
            (new RemovedTree(
                new TreeValue([
                    'items' => new ListValue([
                        new StringValue('a'),
                        new StringValue('b'),
                        new StringValue('c'),
                    ]),
                ]),
                new TreeValue([
                    'items' => new ListValue([new StringValue('a')]),
                ]),
            ))->value(),
            'RemovedTree must reindex the surviving list children so their keys stay sequential',
        );
    }

    #[Test]
    public function reportsFullDottedPathOnNestedCollision(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('RemovedTree cannot remove "haspadar.flag"');

        (new RemovedTree(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'flag' => new BoolValue(true),
                ]),
            ]),
            new TreeValue([
                'haspadar' => new TreeValue([
                    'flag' => new ListValue([]),
                ]),
            ]),
        ))->value();
    }
}
