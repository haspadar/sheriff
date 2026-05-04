<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch\RemoveTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
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
            (new RemoveTree('phpstan.parameters', []))->key(),
            'RemoveTree must expose the configuration key it targets',
        );
    }

    #[Test]
    public function dropsNamedKeyFromBase(): void
    {
        $base = new TreeValue([
            'kept' => new IntValue(1),
            'removed' => new BoolValue(true),
        ]);

        self::assertEquals(
            new TreeValue(['kept' => new IntValue(1)]),
            (new RemoveTree('phpstan.parameters', ['removed']))->applied($base),
            'RemoveTree must drop the entry whose key is listed for removal',
        );
    }

    #[Test]
    public function ignoresKeyAbsentInBase(): void
    {
        $base = new TreeValue(['kept' => new IntValue(1)]);

        self::assertEquals(
            $base,
            (new RemoveTree('phpstan.parameters', ['absent']))->applied($base),
            'RemoveTree must keep base entries when removal targets keys absent from base',
        );
    }

    #[Test]
    public function dropsEveryListedKeyAtOnce(): void
    {
        $base = new TreeValue([
            'kept' => new IntValue(1),
            'first' => new BoolValue(true),
            'second' => new BoolValue(false),
        ]);

        self::assertEquals(
            new TreeValue(['kept' => new IntValue(1)]),
            (new RemoveTree('phpstan.parameters', ['first', 'second']))->applied($base),
            'RemoveTree must drop every key listed for removal in a single application',
        );
    }

    #[Test]
    public function returnsEmptyTreeWhenAllKeysRemoved(): void
    {
        $base = new TreeValue(['only' => new IntValue(1)]);

        self::assertEquals(
            new TreeValue([]),
            (new RemoveTree('phpstan.parameters', ['only']))->applied($base),
            'RemoveTree must return an empty tree when every base entry is removed',
        );
    }

    #[Test]
    public function rejectsBaseValueThatIsNotATree(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('phpstan.parameters');

        (new RemoveTree('phpstan.parameters', []))->applied(new IntValue(8));
    }
}
