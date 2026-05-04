<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Args;

use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\ParsedArgs;
use Haspadar\Sheriff\Formula\Args\UnquotedArgs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UnquotedArgsTest extends TestCase
{
    #[Test]
    public function removesWrappingQuotesFromSingleValue(): void
    {
        $args = new UnquotedArgs(
            new ListArgs(['"a\'b"']),
        );

        self::assertSame(
            ["a'b"],
            $args->values(),
            'UnquotedArgs must remove wrapping double quotes from a single string value',
        );
    }

    #[Test]
    public function removesWrappingQuotesFromParsedListItems(): void
    {
        $args = new UnquotedArgs(
            new ParsedArgs(
                new ListArgs(['["a\'b","c"]']),
            ),
        );

        self::assertSame(
            ["a'b", 'c'],
            $args->values(),
            'UnquotedArgs must remove wrapping quotes from each item in a parsed list',
        );
    }

    #[Test]
    public function leavesUnquotedValueUnchanged(): void
    {
        $args = new UnquotedArgs(
            new ListArgs(['hello']),
        );

        self::assertSame(
            ['hello'],
            $args->values(),
            'UnquotedArgs must leave values that are not wrapped in quotes unchanged',
        );
    }

    #[Test]
    public function returnsEmptyStringForEmptyQuotedString(): void
    {
        $args = new UnquotedArgs(
            new ListArgs(['""']),
        );

        self::assertSame(
            [''],
            $args->values(),
            'UnquotedArgs must return an empty string when the value is an empty quoted string',
        );
    }

    #[Test]
    public function removesOnlySingleMatchingQuoteLayer(): void
    {
        $args = new UnquotedArgs(
            new ListArgs(['""value""']),
        );

        self::assertSame(
            ['"value"'],
            $args->values(),
            'UnquotedArgs must remove only the outermost layer of wrapping quotes',
        );
    }

    #[Test]
    public function preservesMismatchedDoubleAndSingleQuotes(): void
    {
        $args = new UnquotedArgs(
            new ListArgs(['"value\'']),
        );

        self::assertSame(
            ['"value\''],
            $args->values(),
            'UnquotedArgs must not strip mismatched quote types',
        );
    }

    #[Test]
    public function preservesMismatchedSingleAndDoubleQuotes(): void
    {
        $args = new UnquotedArgs(
            new ListArgs(['\'value"']),
        );

        self::assertSame(
            ['\'value"'],
            $args->values(),
            'UnquotedArgs must not strip when opening single quote does not match closing double quote',
        );
    }

    #[Test]
    public function removesSingleQuotes(): void
    {
        $args = new UnquotedArgs(
            new ListArgs(["'hello'"]),
        );

        self::assertSame(
            ['hello'],
            $args->values(),
            'UnquotedArgs must remove matching single quotes',
        );
    }
}
