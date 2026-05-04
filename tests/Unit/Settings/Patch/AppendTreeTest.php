<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch\AppendTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
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
        $base = new TreeValue(['existing' => new IntValue(1)]);
        $extra = new TreeValue(['added' => new BoolValue(true)]);

        self::assertEquals(
            new TreeValue([
                'existing' => new IntValue(1),
                'added' => new BoolValue(true),
            ]),
            (new AppendTree('phpstan.parameters', $extra))->applied($base),
            'AppendTree must add a key from the extra tree when it is absent in the base',
        );
    }

    #[Test]
    public function keepsExistingKeyWhenAlsoPresentInExtra(): void
    {
        $base = new TreeValue(['flag' => new BoolValue(false)]);
        $extra = new TreeValue(['flag' => new BoolValue(true)]);

        self::assertEquals(
            new TreeValue(['flag' => new BoolValue(false)]),
            (new AppendTree('phpstan.parameters', $extra))->applied($base),
            'AppendTree must keep the base entry when the same key is present in the extra tree',
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
    public function rejectsBaseValueThatIsNotATree(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('phpstan.parameters');

        (new AppendTree('phpstan.parameters', new TreeValue([])))->applied(new IntValue(8));
    }
}
