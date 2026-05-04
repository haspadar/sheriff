<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Args;

use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\StringifiedArgs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StringifiedArgsTest extends TestCase
{
    #[Test]
    public function convertsBooleanTrueToLiteralTrue(): void
    {
        $args = new StringifiedArgs(
            new ListArgs([true]),
        );

        self::assertSame(
            ['true'],
            $args->values(),
            'StringifiedArgs must convert boolean true to the literal string "true"',
        );
    }

    #[Test]
    public function convertsBooleanFalseToLiteralFalse(): void
    {
        $args = new StringifiedArgs(
            new ListArgs([false]),
        );

        self::assertSame(
            ['false'],
            $args->values(),
            'StringifiedArgs must convert boolean false to the literal string "false"',
        );
    }

    #[Test]
    public function convertsMixedValuesToStrings(): void
    {
        $args = new StringifiedArgs(
            new ListArgs([
                true,
                false,
                10,
                3.14,
                'x',
            ]),
        );

        self::assertSame(
            ['true', 'false', '10', '3.14', 'x'],
            $args->values(),
            'StringifiedArgs must convert all scalar types to their string representations',
        );
    }
}
