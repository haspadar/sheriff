<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonList;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeonListTest extends TestCase
{
    #[Test]
    public function rendersEmptyListAsEmptySequence(): void
    {
        self::assertSame(
            '[]',
            (new NeonList(new ListValue([])))->rendered(),
            'NeonList must render an empty list as the neon empty-flow sequence',
        );
    }

    #[Test]
    public function rendersStringEntriesAsQuotedFlowSequence(): void
    {
        self::assertSame(
            '["src", "tests"]',
            (new NeonList(new ListValue([
                new StringValue('src'),
                new StringValue('tests'),
            ])))->rendered(),
            'NeonList must render string entries as a quoted flow sequence',
        );
    }

    #[Test]
    public function rendersMixedScalarEntries(): void
    {
        self::assertSame(
            '[true, 8, "foo"]',
            (new NeonList(new ListValue([
                new BoolValue(true),
                new IntValue(8),
                new StringValue('foo'),
            ])))->rendered(),
            'NeonList must render each entry through its matching neon renderer',
        );
    }
}
