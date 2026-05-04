<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Args;

use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\ParsedArgs;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParsedArgsTest extends TestCase
{
    #[Test]
    public function parsesJsonListLiteralIntoValues(): void
    {
        self::assertSame(
            ['one', 'two', 'three'],
            (new ParsedArgs(
                new ListArgs(['["one","two","three"]']),
            ))->values(),
            'ParsedArgs must parse a JSON list of strings into individual values',
        );
    }

    #[Test]
    public function parsesJsonListWithNumbers(): void
    {
        self::assertSame(
            [1, 2, 3],
            (new ParsedArgs(
                new ListArgs(['[1,2,3]']),
            ))->values(),
            'ParsedArgs must parse a JSON list of numbers into individual values',
        );
    }

    #[Test]
    public function parsesJsonListWithBooleans(): void
    {
        self::assertSame(
            [true, false],
            (new ParsedArgs(
                new ListArgs(['[true,false]']),
            ))->values(),
            'ParsedArgs must parse a JSON list of booleans into individual values',
        );
    }

    #[Test]
    public function parsesJsonListWithMixedScalars(): void
    {
        self::assertSame(
            ['x', 42, false],
            (new ParsedArgs(
                new ListArgs(['["x",42,false]']),
            ))->values(),
            'ParsedArgs must parse a JSON list of mixed scalar types into individual values',
        );
    }

    #[Test]
    public function parsesJsonListWithCommaInsideQuotedString(): void
    {
        self::assertSame(
            ['a,b', 'c'],
            (new ParsedArgs(
                new ListArgs(['["a,b","c"]']),
            ))->values(),
            'ParsedArgs must treat commas inside quoted strings as part of the value',
        );
    }

    #[Test]
    public function returnsEmptyListWhenLiteralIsEmpty(): void
    {
        self::assertSame(
            [],
            (new ParsedArgs(
                new ListArgs(['[]']),
            ))->values(),
            'ParsedArgs must return an empty list when the JSON literal is an empty array',
        );
    }

    #[Test]
    public function throwsWhenInputIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs([]),
        ))->values();
    }

    #[Test]
    public function throwsWhenMoreThanOneLiteralProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(['[1,2]', '[3,4]']),
        ))->values();
    }

    #[Test]
    public function throwsWhenFirstElementIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs([123]),
        ))->values();
    }

    #[Test]
    public function throwsWhenLiteralIsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(['']),
        ))->values();
    }

    #[Test]
    public function throwsWhenInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(['alpha,beta']),
        ))->values();
    }

    #[Test]
    public function throwsWhenUsingSingleQuotes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(["['a','b']"]),
        ))->values();
    }

    #[Test]
    public function throwsWhenTrailingCommaPresent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(['["a","b",]']),
        ))->values();
    }

    #[Test]
    public function throwsWhenLiteralIsNotList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(['{"a":1}']),
        ))->values();
    }

    #[Test]
    public function throwsWhenListContainsNonScalar(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(['[{"a":1}]']),
        ))->values();
    }

    #[Test]
    public function throwsWhenLiteralIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParsedArgs(
            new ListArgs(['null']),
        ))->values();
    }
}
