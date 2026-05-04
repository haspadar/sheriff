<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\FormatAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FormatActionTest extends TestCase
{
    #[Test]
    public function formatsSingleValue(): void
    {
        $result = (new FormatAction('prefix: %s'))
            ->transformed(new ListArgs(['value']));

        self::assertSame(
            ['prefix: value'],
            $result->values(),
            'FormatAction must apply the template to a single input value',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsEmpty(): void
    {
        self::assertThat(
            (new FormatAction('%s'))->transformed(new ListArgs([])),
            new HasArgsValues([]),
            'FormatAction must pass empty list through unchanged',
        );
    }

    #[Test]
    public function throwsWhenInputContainsMultipleValues(): void
    {
        $this->expectException(SheriffException::class);

        (new FormatAction('%s'))
            ->transformed(new ListArgs(['a', 'b']));
    }

    #[Test]
    public function normalizesNewlineInTemplate(): void
    {
        $result = (new FormatAction('a\\n%s'))
            ->transformed(new ListArgs(['b']));

        self::assertSame(
            ["a\nb"],
            $result->values(),
            'FormatAction must normalize \\n to real newline in template',
        );
    }

    #[Test]
    public function normalizesCarriageReturnInTemplate(): void
    {
        $result = (new FormatAction('a\\r%s'))
            ->transformed(new ListArgs(['b']));

        self::assertSame(
            ["a\rb"],
            $result->values(),
            'FormatAction must normalize \\r to real carriage return in template',
        );
    }

    #[Test]
    public function normalizesTabInTemplate(): void
    {
        $result = (new FormatAction('a\\t%s'))
            ->transformed(new ListArgs(['b']));

        self::assertSame(
            ["a\tb"],
            $result->values(),
            'FormatAction must normalize \\t to real tab in template',
        );
    }

    #[Test]
    public function normalizesEscapedBackslashInTemplate(): void
    {
        $result = (new FormatAction('a\\\\%s'))
            ->transformed(new ListArgs(['b']));

        self::assertSame(
            ['a\\b'],
            $result->values(),
            'FormatAction must normalize \\\\\\\\ to single backslash in template',
        );
    }

    #[Test]
    public function throwsWhenSprintfFails(): void
    {
        $this->expectException(SheriffException::class);

        (new FormatAction('%1$s %2$s'))
            ->transformed(new ListArgs(['only-one']));
    }

    #[Test]
    public function throwsWhenSprintfRaisesValueError(): void
    {
        $this->expectException(SheriffException::class);

        (new FormatAction('"%z"'))
            ->transformed(new ListArgs(['a']));
    }
}
