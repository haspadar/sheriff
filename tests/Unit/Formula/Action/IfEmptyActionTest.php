<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\IfEmptyAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IfEmptyActionTest extends TestCase
{
    #[Test]
    public function passesEmptyListThrough(): void
    {
        self::assertThat(
            (new IfEmptyAction())->transformed(new ListArgs([])),
            new HasArgsValues([]),
            'IfEmptyAction must pass empty list through unchanged',
        );
    }

    #[Test]
    public function passesSingleEmptyStringThrough(): void
    {
        self::assertThat(
            (new IfEmptyAction())->transformed(new ListArgs([''])),
            new HasArgsValues(['']),
            'IfEmptyAction must pass single empty string through unchanged',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsNonEmpty(): void
    {
        self::assertThat(
            (new IfEmptyAction())->transformed(new ListArgs(['hello'])),
            new HasArgsValues([]),
            'IfEmptyAction must return empty list for non-empty input',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsNumeric(): void
    {
        self::assertThat(
            (new IfEmptyAction())->transformed(new ListArgs([42])),
            new HasArgsValues([]),
            'IfEmptyAction must return empty list for numeric input',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsNumericZero(): void
    {
        self::assertThat(
            (new IfEmptyAction())->transformed(new ListArgs([0])),
            new HasArgsValues([]),
            'IfEmptyAction must return empty list for numeric zero',
        );
    }

    #[Test]
    public function returnsEmptyListWhenInputIsBooleanFalse(): void
    {
        self::assertThat(
            (new IfEmptyAction())->transformed(new ListArgs([false])),
            new HasArgsValues([]),
            'IfEmptyAction must return empty list for boolean false',
        );
    }
}
