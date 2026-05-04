<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\ShellQuoteAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ShellQuoteActionTest extends TestCase
{
    #[Test]
    public function quotesPlainValue(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs(['value'])),
            new HasArgsValues(["'value'"]),
            'ShellQuoteAction must wrap a plain value in single quotes',
        );
    }

    #[Test]
    public function preservesSpacesInsideQuotes(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs(['hello world'])),
            new HasArgsValues(["'hello world'"]),
            'ShellQuoteAction must keep spaces intact inside single quotes',
        );
    }

    #[Test]
    public function escapesEmbeddedSingleQuote(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs(["it's"])),
            new HasArgsValues(["'it'\\''s'"]),
            "ShellQuoteAction must replace embedded ' with '\\'' to keep POSIX quoting",
        );
    }

    #[Test]
    public function leavesDollarSignLiteral(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs(['$HOME'])),
            new HasArgsValues(["'\$HOME'"]),
            'ShellQuoteAction must keep $ literal because single quotes suppress expansion',
        );
    }

    #[Test]
    public function leavesBacktickLiteral(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs(['`pwd`'])),
            new HasArgsValues(["'`pwd`'"]),
            'ShellQuoteAction must keep backticks literal because single quotes suppress command substitution',
        );
    }

    #[Test]
    public function leavesBackslashLiteral(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs(['a\\b'])),
            new HasArgsValues(["'a\\b'"]),
            'ShellQuoteAction must keep a backslash literal inside single quotes',
        );
    }

    #[Test]
    public function returnsEmptyQuotedTokenForEmptyString(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs([''])),
            new HasArgsValues(["''"]),
            'ShellQuoteAction must render empty string as a quoted empty token',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsEmpty(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs([])),
            new HasArgsValues([]),
            'ShellQuoteAction must return empty list when no values are provided',
        );
    }

    #[Test]
    public function quotesEachListElementSeparately(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs(['a b', "c'd", 'e'])),
            new HasArgsValues(["'a b'", "'c'\\''d'", "'e'"]),
            'ShellQuoteAction must quote each list element independently',
        );
    }

    #[Test]
    public function coercesScalarsToQuotedStrings(): void
    {
        self::assertThat(
            (new ShellQuoteAction())
                ->transformed(new ListArgs([42, true, false])),
            new HasArgsValues(["'42'", "'true'", "'false'"]),
            'ShellQuoteAction must stringify scalars via StringifiedArgs before quoting',
        );
    }
}
