<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Args;

use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\ParsedArgs;
use Haspadar\Sheriff\Formula\Args\TrimmedArgs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TrimmedArgsTest extends TestCase
{
    #[Test]
    public function trimsSingleStringValue(): void
    {
        self::assertSame(
            ['abc'],
            (new TrimmedArgs(new ListArgs(['  abc  '])))->values(),
            'TrimmedArgs must strip surrounding whitespace from string values',
        );
    }

    #[Test]
    public function doesNotModifyNonStringValues(): void
    {
        self::assertSame(
            [1, true, 3.14],
            (new TrimmedArgs(new ListArgs([1, true, 3.14])))->values(),
            'TrimmedArgs must leave non-string values unchanged',
        );
    }

    #[Test]
    public function trimsItemsInsideParsedJsonList(): void
    {
        self::assertSame(
            ['a', 'b'],
            (new TrimmedArgs(
                new ParsedArgs(new ListArgs(['[" a "," b "]'])),
            ))->values(),
            'TrimmedArgs must trim whitespace from each string item in a parsed JSON list',
        );
    }

    #[Test]
    public function trimsMixedParsedJsonList(): void
    {
        self::assertSame(
            ['x', 42, false],
            (new TrimmedArgs(
                new ParsedArgs(new ListArgs(['[" x ",42,false]'])),
            ))->values(),
            'TrimmedArgs must trim only string items in a mixed-type parsed JSON list',
        );
    }
}
