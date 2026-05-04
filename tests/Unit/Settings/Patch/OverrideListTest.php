<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch\OverrideList;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OverrideListTest extends TestCase
{
    #[Test]
    public function exposesTargetKey(): void
    {
        self::assertSame(
            'phpstan.paths',
            (new OverrideList('phpstan.paths', new ListValue([])))->key(),
            'OverrideList must expose the configuration key it targets',
        );
    }

    #[Test]
    public function replacesBaseWithReplacementList(): void
    {
        $replacement = new ListValue([new StringValue('lib')]);
        $base = new ListValue([new StringValue('src'), new StringValue('tests')]);

        self::assertEquals(
            $replacement,
            (new OverrideList('phpstan.paths', $replacement))->applied($base),
            'OverrideList must replace the base list with the replacement list',
        );
    }

    #[Test]
    public function ignoresBaseEntriesEntirely(): void
    {
        self::assertEquals(
            new ListValue([]),
            (new OverrideList('phpstan.paths', new ListValue([])))
                ->applied(new ListValue([new StringValue('src')])),
            'OverrideList must replace the base list with an empty list when the override is empty',
        );
    }
}
