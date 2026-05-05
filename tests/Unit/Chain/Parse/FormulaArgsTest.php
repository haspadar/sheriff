<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Parse;

use Haspadar\Sheriff\Chain\Parse\FormulaArgs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FormulaArgsTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenOnlyWhitespaceIsGiven(): void
    {
        self::assertSame(
            [],
            (new FormulaArgs('   '))->values(),
            'FormulaArgs must treat whitespace-only argument lists as empty',
        );
    }

    #[Test]
    public function unquotesEmptyStringArgument(): void
    {
        self::assertSame(
            [''],
            (new FormulaArgs('""'))->values(),
            'FormulaArgs must unquote empty string literals',
        );
    }

    #[Test]
    public function keepsMismatchedQuotesVerbatim(): void
    {
        self::assertSame(
            ['"value'],
            (new FormulaArgs('"value'))->values(),
            'FormulaArgs must only unquote arguments whose opening and closing quotes match',
        );
    }

    #[Test]
    public function unescapesKnownAndUnknownSequences(): void
    {
        self::assertSame(
            ["\n\r\t\\\"'\\x"],
            (new FormulaArgs('"\\n\\r\\t\\\\\\"\\\'\\x"'))->values(),
            'FormulaArgs must unescape known sequences and preserve unknown escapes',
        );
    }
}
