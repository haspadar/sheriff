<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch\RemoveTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class RemoveTreeTest extends TestCase
{
    #[Test]
    public function exposesTargetKey(): void
    {
        self::assertSame(
            'phpstan.parameters',
            (new RemoveTree('phpstan.parameters', new TreeValue([])))->key(),
            'RemoveTree must expose the configuration key it targets',
        );
    }

    #[Test]
    public function recursesIntoNestedTreeAndDropsListEntries(): void
    {
        self::assertEquals(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'excludedClasses' => new ListValue([new StringValue('\\Kept')]),
                    ]),
                ]),
            ]),
            (new RemoveTree(
                'phpstan.parameters',
                new TreeValue([
                    'haspadar' => new TreeValue([
                        'afferentCoupling' => new TreeValue([
                            'excludedClasses' => new ListValue([new StringValue('\\Dropped')]),
                        ]),
                    ]),
                ]),
            ))->applied(new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'excludedClasses' => new ListValue([
                            new StringValue('\\Kept'),
                            new StringValue('\\Dropped'),
                        ]),
                    ]),
                ]),
            ])),
            'RemoveTree must walk through nested trees and prune matching list leaves',
        );
    }

    #[Test]
    public function dropsNamedKeysFromTreeWhenSpecAtSameKeyIsAListOfStrings(): void
    {
        self::assertEquals(
            new TreeValue([
                'flags' => new TreeValue([
                    'kept' => new IntValue(1),
                ]),
            ]),
            (new RemoveTree(
                'phpstan.parameters',
                new TreeValue([
                    'flags' => new ListValue([new StringValue('removed')]),
                ]),
            ))->applied(new TreeValue([
                'flags' => new TreeValue([
                    'kept' => new IntValue(1),
                    'removed' => new BoolValue(true),
                ]),
            ])),
            'RemoveTree must drop named keys from a base tree when the spec at the same key is a list of strings',
        );
    }

    #[Test]
    public function ignoresKeyAbsentInBase(): void
    {
        $base = new TreeValue(['kept' => new IntValue(1)]);

        self::assertEquals(
            $base,
            (new RemoveTree(
                'phpstan.parameters',
                new TreeValue(['absent' => new ListValue([])]),
            ))->applied($base),
            'RemoveTree must keep base entries when the spec targets keys absent from base',
        );
    }

    #[Test]
    public function acceptsEmptyListBaseAsEmptyTree(): void
    {
        self::assertEquals(
            new TreeValue([]),
            (new RemoveTree('envs', new TreeValue([])))->applied(new ListValue([])),
            'RemoveTree must accept an empty ListValue base because YAML `{}` parses as `[]`',
        );
    }

    #[Test]
    public function dropsNothingFromEmptyListBaseEvenWithNonEmptySpec(): void
    {
        self::assertEquals(
            new TreeValue([]),
            (new RemoveTree(
                'envs',
                new TreeValue(['absent' => new ListValue([new StringValue('foo')])]),
            ))->applied(new ListValue([])),
            'RemoveTree must treat an empty ListValue base as an empty TreeValue regardless of spec contents',
        );
    }

    #[Test]
    public function rejectsBaseValueThatIsNotATree(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('phpstan.parameters');

        (new RemoveTree('phpstan.parameters', new TreeValue([])))->applied(new IntValue(8));
    }
}
