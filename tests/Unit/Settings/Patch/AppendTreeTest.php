<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch\AppendTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class AppendTreeTest extends TestCase
{
    #[Test]
    public function exposesTargetKey(): void
    {
        self::assertSame(
            'phpstan.parameters',
            (new AppendTree('phpstan.parameters', new TreeValue([])))->key(),
            'AppendTree must expose the configuration key it targets',
        );
    }

    #[Test]
    public function addsKeyAbsentInBase(): void
    {
        self::assertEquals(
            new TreeValue([
                'existing' => new IntValue(1),
                'added' => new BoolValue(true),
            ]),
            (new AppendTree(
                'phpstan.parameters',
                new TreeValue(['added' => new BoolValue(true)]),
            ))->applied(new TreeValue(['existing' => new IntValue(1)])),
            'AppendTree must add a key from the extra tree when it is absent in the base',
        );
    }

    #[Test]
    public function appendsListEntriesAtNestedLeaf(): void
    {
        self::assertEquals(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'excludedClasses' => new ListValue([
                        new StringValue('A'),
                        new StringValue('B'),
                    ]),
                ]),
            ]),
            (new AppendTree(
                'phpstan.parameters',
                new TreeValue([
                    'haspadar' => new TreeValue([
                        'excludedClasses' => new ListValue([new StringValue('B')]),
                    ]),
                ]),
            ))->applied(new TreeValue([
                'haspadar' => new TreeValue([
                    'excludedClasses' => new ListValue([new StringValue('A')]),
                ]),
            ])),
            'AppendTree must walk through nested trees and concatenate matching list leaves',
        );
    }

    #[Test]
    public function appendsThroughThreeLevelsAsInIssueBody(): void
    {
        self::assertEquals(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'excludedClasses' => new ListValue([
                            new StringValue('\\Existing\\E'),
                            new StringValue('\\App\\MyException'),
                        ]),
                    ]),
                ]),
            ]),
            (new AppendTree(
                'phpstan.parameters',
                new TreeValue([
                    'haspadar' => new TreeValue([
                        'afferentCoupling' => new TreeValue([
                            'excludedClasses' => new ListValue([
                                new StringValue('\\App\\MyException'),
                            ]),
                        ]),
                    ]),
                ]),
            ))->applied(new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'excludedClasses' => new ListValue([
                            new StringValue('\\Existing\\E'),
                        ]),
                    ]),
                ]),
            ])),
            'AppendTree must reach the list leaf three levels deep when the user appends nested phpstan parameters',
        );
    }

    #[Test]
    public function returnsBaseUnchangedWhenExtraIsEmpty(): void
    {
        $base = new TreeValue(['existing' => new IntValue(1)]);

        self::assertEquals(
            $base,
            (new AppendTree('phpstan.parameters', new TreeValue([])))->applied($base),
            'AppendTree must return the base tree unchanged when no entries are appended',
        );
    }

    #[Test]
    public function acceptsEmptyListBaseAsEmptyTree(): void
    {
        self::assertEquals(
            new TreeValue(['added' => new BoolValue(true)]),
            (new AppendTree(
                'envs',
                new TreeValue(['added' => new BoolValue(true)]),
            ))->applied(new ListValue([])),
            'AppendTree must accept an empty ListValue base because YAML `{}` parses as `[]`',
        );
    }

    #[Test]
    public function rejectsBaseValueThatIsNotATree(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('phpstan.parameters');

        (new AppendTree('phpstan.parameters', new TreeValue([])))->applied(new IntValue(8));
    }

    #[Test]
    public function rejectsScalarCollisionBetweenBaseAndExtra(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('AppendedTree cannot merge "flag"');

        (new AppendTree(
            'phpstan.parameters',
            new TreeValue(['flag' => new BoolValue(true)]),
        ))->applied(new TreeValue(['flag' => new BoolValue(false)]));
    }
}
