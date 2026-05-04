<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\JoinAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JoinActionTest extends TestCase
{
    #[Test]
    public function joinsValuesWithDelimiter(): void
    {
        self::assertThat(
            (new JoinAction(' | '))
                ->transformed(new ListArgs(['red', 'green', 'blue'])),
            new HasArgsValues(['red | green | blue']),
            'JoinAction must join values using the given delimiter',
        );
    }

    #[Test]
    public function joinsUsingEmptyDelimiter(): void
    {
        self::assertThat(
            (new JoinAction(''))
                ->transformed(new ListArgs(['a', 'b'])),
            new HasArgsValues(['ab']),
            'JoinAction must concatenate values without a separator when the delimiter is empty',
        );
    }

    #[Test]
    public function joinsSingleValue(): void
    {
        self::assertThat(
            (new JoinAction(','))
                ->transformed(new ListArgs(['one'])),
            new HasArgsValues(['one']),
            'JoinAction must return the single value unchanged when the list has one element',
        );
    }

    #[Test]
    public function returnsEmptyStringWhenInputIsEmpty(): void
    {
        self::assertThat(
            (new JoinAction(','))
                ->transformed(new ListArgs([])),
            new HasArgsValues(['']),
            'JoinAction must return a single empty string when no values are provided',
        );
    }

    #[Test]
    public function interpretsNewlineEscape(): void
    {
        self::assertThat(
            (new JoinAction('\n'))
                ->transformed(new ListArgs(['a', 'b', 'c'])),
            new HasArgsValues(["a\nb\nc"]),
            'JoinAction must interpret \\n as a newline character in the delimiter',
        );
    }

    #[Test]
    public function interpretsTabEscape(): void
    {
        self::assertThat(
            (new JoinAction('\t'))
                ->transformed(new ListArgs(['a', 'b'])),
            new HasArgsValues(["a\tb"]),
            'JoinAction must interpret \\t as a tab character in the delimiter',
        );
    }

    #[Test]
    public function interpretsBackslashEscape(): void
    {
        self::assertThat(
            (new JoinAction('\\\\'))
                ->transformed(new ListArgs(['a', 'b'])),
            new HasArgsValues(['a\\b']),
            'JoinAction must interpret \\\\ as a literal backslash in the delimiter',
        );
    }
}
