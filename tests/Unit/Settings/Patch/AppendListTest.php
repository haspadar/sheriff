<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch\AppendList;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class AppendListTest extends TestCase
{
    #[Test]
    public function exposesTargetKey(): void
    {
        self::assertSame(
            'infra.exclude',
            (new AppendList('infra.exclude', new ListValue([])))->key(),
            'AppendList must expose the configuration key it targets',
        );
    }

    #[Test]
    public function appendsExtraEntriesAfterBaseEntries(): void
    {
        $base = new ListValue([new StringValue('vendor'), new StringValue('tests')]);
        $extra = new ListValue([new StringValue('dist')]);

        self::assertEquals(
            new ListValue([
                new StringValue('vendor'),
                new StringValue('tests'),
                new StringValue('dist'),
            ]),
            (new AppendList('infra.exclude', $extra))->applied($base),
            'AppendList must append the extra entries after the base entries',
        );
    }

    #[Test]
    public function returnsBaseUnchangedWhenExtraIsEmpty(): void
    {
        $base = new ListValue([new StringValue('vendor')]);

        self::assertEquals(
            $base,
            (new AppendList('infra.exclude', new ListValue([])))->applied($base),
            'AppendList must return the base list unchanged when no entries are appended',
        );
    }

    #[Test]
    public function rejectsBaseValueThatIsNotAList(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('infra.exclude');

        (new AppendList('infra.exclude', new ListValue([])))->applied(new IntValue(8));
    }
}
