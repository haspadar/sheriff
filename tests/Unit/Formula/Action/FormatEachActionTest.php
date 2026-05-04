<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\FormatEachAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FormatEachActionTest extends TestCase
{
    #[Test]
    public function formatsSingleStringValue(): void
    {
        $result = (new FormatEachAction('ext=%s'))
            ->transformed(new ListArgs(['mbstring']));

        self::assertSame(
            ['ext=mbstring'],
            $result->values(),
            'FormatEachAction must apply the template to a single string value',
        );
    }

    #[Test]
    public function formatsMultipleValues(): void
    {
        $result = (new FormatEachAction('v=%s'))
            ->transformed(new ListArgs(['a', 'b']));

        self::assertSame(
            ['v=a', 'v=b'],
            $result->values(),
            'FormatEachAction must apply the template to each value in the list',
        );
    }

    #[Test]
    public function formatsBooleanValuesUsingCanonicalStringRepresentation(): void
    {
        $result = (new FormatEachAction('flag=%s'))
            ->transformed(new ListArgs([true, false]));

        self::assertSame(
            ['flag=true', 'flag=false'],
            $result->values(),
            'FormatEachAction must convert booleans to their canonical string representation',
        );
    }

    #[Test]
    public function formatsNumericValues(): void
    {
        $result = (new FormatEachAction('n=%s'))
            ->transformed(new ListArgs([10, 3.5]));

        self::assertSame(
            ['n=10', 'n=3.5'],
            $result->values(),
            'FormatEachAction must format integer and float values using the template',
        );
    }

    #[Test]
    public function formatsUsingEmptyTemplate(): void
    {
        $result = (new FormatEachAction(''))
            ->transformed(new ListArgs(['a', 'b']));

        self::assertSame(
            ['', ''],
            $result->values(),
            'FormatEachAction must produce empty strings when the template is empty',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsEmpty(): void
    {
        $result = (new FormatEachAction('%s'))
            ->transformed(new ListArgs([]));

        self::assertSame(
            [],
            $result->values(),
            'FormatEachAction must return an empty list when no values are provided',
        );
    }
}
