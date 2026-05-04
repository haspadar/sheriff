<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch\OverrideTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class OverrideTreeTest extends TestCase
{
    #[Test]
    public function exposesTargetKey(): void
    {
        self::assertSame(
            'phpstan.parameters',
            (new OverrideTree('phpstan.parameters', new TreeValue([])))->key(),
            'OverrideTree must expose the configuration key it targets',
        );
    }

    #[Test]
    public function addsNewKeyWithoutTouchingBaseEntries(): void
    {
        $base = new TreeValue(['existing' => new IntValue(1)]);
        $patch = new TreeValue(['added' => new BoolValue(true)]);

        self::assertEquals(
            new TreeValue([
                'existing' => new IntValue(1),
                'added' => new BoolValue(true),
            ]),
            (new OverrideTree('phpstan.parameters', $patch))->applied($base),
            'OverrideTree must add new keys without touching existing base entries',
        );
    }

    #[Test]
    public function replacesExistingLeafValue(): void
    {
        $base = new TreeValue(['flag' => new BoolValue(false)]);
        $patch = new TreeValue(['flag' => new BoolValue(true)]);

        self::assertEquals(
            new TreeValue(['flag' => new BoolValue(true)]),
            (new OverrideTree('phpstan.parameters', $patch))->applied($base),
            'OverrideTree must replace an existing leaf value with the override',
        );
    }

    #[Test]
    public function descendsThroughNestedTreesPreservingSiblings(): void
    {
        $base = new TreeValue([
            'haspadar' => new TreeValue([
                'afferentCoupling' => new TreeValue([
                    'ignoreInterfaces' => new BoolValue(true),
                    'ignoreAbstract' => new BoolValue(false),
                ]),
            ]),
        ]);
        $patch = new TreeValue([
            'haspadar' => new TreeValue([
                'afferentCoupling' => new TreeValue([
                    'ignoreAbstract' => new BoolValue(true),
                ]),
            ]),
        ]);

        self::assertEquals(
            new TreeValue([
                'haspadar' => new TreeValue([
                    'afferentCoupling' => new TreeValue([
                        'ignoreInterfaces' => new BoolValue(true),
                        'ignoreAbstract' => new BoolValue(true),
                    ]),
                ]),
            ]),
            (new OverrideTree('phpstan.parameters', $patch))->applied($base),
            'OverrideTree must descend into nested trees and keep sibling entries intact',
        );
    }

    #[Test]
    public function replacesEntireBranchWhenOverrideIsNotTree(): void
    {
        $base = new TreeValue([
            'tag' => new TreeValue(['name' => new StringValue('old')]),
        ]);
        $patch = new TreeValue(['tag' => new StringValue('new')]);

        self::assertEquals(
            new TreeValue(['tag' => new StringValue('new')]),
            (new OverrideTree('phpstan.parameters', $patch))->applied($base),
            'OverrideTree must replace the whole base branch when override leaf is not a tree',
        );
    }

    #[Test]
    public function rejectsBaseValueThatIsNotATree(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('phpstan.parameters');

        (new OverrideTree('phpstan.parameters', new TreeValue([])))->applied(new IntValue(8));
    }
}
