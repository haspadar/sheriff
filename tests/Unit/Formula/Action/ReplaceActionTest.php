<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\ReplaceAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReplaceActionTest extends TestCase
{
    #[Test]
    public function replacesSubstringInSingleValue(): void
    {
        self::assertThat(
            (new ReplaceAction('".", "x"'))
                ->transformed(new ListArgs(['8.3'])),
            new HasArgsValues(['8x3']),
            'ReplaceAction must replace every occurrence of the search substring',
        );
    }

    #[Test]
    public function replacesSubstringInEachValue(): void
    {
        self::assertThat(
            (new ReplaceAction('".", "x"'))
                ->transformed(new ListArgs(['8.3', '8.4'])),
            new HasArgsValues(['8x3', '8x4']),
            'ReplaceAction must apply the replacement to every value in the list',
        );
    }

    #[Test]
    public function leavesValueUnchangedWhenSearchNotFound(): void
    {
        self::assertThat(
            (new ReplaceAction('".", "x"'))
                ->transformed(new ListArgs(['8'])),
            new HasArgsValues(['8']),
            'ReplaceAction must leave values unchanged when the search substring is absent',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsEmpty(): void
    {
        self::assertThat(
            (new ReplaceAction('".", "x"'))
                ->transformed(new ListArgs([])),
            new HasArgsValues([]),
            'ReplaceAction must return an empty list when no values are provided',
        );
    }

    #[Test]
    public function interpretsNewlineEscapeInReplacement(): void
    {
        self::assertThat(
            (new ReplaceAction('"|", "\n"'))
                ->transformed(new ListArgs(['a|b'])),
            new HasArgsValues(["a\nb"]),
            'ReplaceAction must interpret \\n as a newline character in the replacement',
        );
    }

    #[Test]
    public function stringifiesBooleanValuesBeforeReplacement(): void
    {
        self::assertThat(
            (new ReplaceAction('"u", "U"'))
                ->transformed(new ListArgs([true])),
            new HasArgsValues(['trUe']),
            'ReplaceAction must convert booleans to their canonical string representation before replacing',
        );
    }

    #[Test]
    public function throwsWhenArgumentsAreMissing(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Action "replace" requires two arguments: search and replace');

        (new ReplaceAction('"."'))
            ->transformed(new ListArgs(['8.3']));
    }
}
