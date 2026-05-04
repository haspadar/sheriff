<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\IfNotEmptyAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IfNotEmptyActionTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenInputIsEmpty(): void
    {
        self::assertThat(
            (new IfNotEmptyAction())->transformed(new ListArgs([])),
            new HasArgsValues([]),
            'IfNotEmptyAction must return empty list for empty input',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsSingleEmptyString(): void
    {
        self::assertThat(
            (new IfNotEmptyAction())->transformed(new ListArgs([''])),
            new HasArgsValues([]),
            'IfNotEmptyAction must return empty list for single empty string',
        );
    }

    #[Test]
    public function passesNonEmptyValueThrough(): void
    {
        self::assertThat(
            (new IfNotEmptyAction())->transformed(new ListArgs(['hello'])),
            new HasArgsValues(['hello']),
            'IfNotEmptyAction must pass non-empty value through unchanged',
        );
    }

    #[Test]
    public function passesNumericValueThrough(): void
    {
        self::assertThat(
            (new IfNotEmptyAction())->transformed(new ListArgs([0])),
            new HasArgsValues([0]),
            'IfNotEmptyAction must treat numeric zero as non-empty',
        );
    }

    #[Test]
    public function passesBooleanFalseThrough(): void
    {
        self::assertThat(
            (new IfNotEmptyAction())->transformed(new ListArgs([false])),
            new HasArgsValues([false]),
            'IfNotEmptyAction must treat boolean false as non-empty',
        );
    }

    #[Test]
    public function passesMultipleValuesThrough(): void
    {
        self::assertThat(
            (new IfNotEmptyAction())->transformed(new ListArgs(['a', 'b'])),
            new HasArgsValues(['a', 'b']),
            'IfNotEmptyAction must pass multiple values through unchanged',
        );
    }

    #[Test]
    public function passesMultipleEmptyStringsThrough(): void
    {
        self::assertThat(
            (new IfNotEmptyAction())->transformed(new ListArgs(['', ''])),
            new HasArgsValues(['', '']),
            'IfNotEmptyAction must treat multiple empty strings as non-empty',
        );
    }
}
