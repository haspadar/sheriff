<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Formula\Action\FirstAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FirstActionTest extends TestCase
{
    #[Test]
    public function returnsFirstValueFromList(): void
    {
        self::assertThat(
            (new FirstAction())->transformed(new ListArgs(['8.3', '8.4', '8.5'])),
            new HasArgsValues(['8.3']),
            'FirstAction must return only the first element of the list',
        );
    }

    #[Test]
    public function returnsSingleValueUnchanged(): void
    {
        self::assertThat(
            (new FirstAction())->transformed(new ListArgs(['8.3'])),
            new HasArgsValues(['8.3']),
            'FirstAction must return the single value unchanged',
        );
    }

    #[Test]
    public function returnsEmptyStringWhenInputIsEmpty(): void
    {
        self::assertThat(
            (new FirstAction())->transformed(new ListArgs([])),
            new HasArgsValues(['']),
            'FirstAction must return a single empty string when no values are provided',
        );
    }
}
