<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula;

use Haspadar\Sheriff\Formula\ExecutedFormula;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Constraint\Formula\HasFormulaResult;
use Haspadar\Sheriff\Tests\Fake\Formula\FakeAction;
use Haspadar\Sheriff\Tests\Fake\Formula\FakeActions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExecutedFormulaTest extends TestCase
{
    #[Test]
    public function returnsEmptyStringWhenNoActionsProduceValues(): void
    {
        self::assertThat(
            new ExecutedFormula(new FakeActions([])),
            new HasFormulaResult(''),
            'ExecutedFormula must return an empty string when no actions produce values',
        );
    }

    #[Test]
    public function returnsSingleValueAsString(): void
    {
        self::assertThat(
            new ExecutedFormula(
                new FakeActions([
                    new FakeAction(['ok']),
                ]),
            ),
            new HasFormulaResult('ok'),
            'ExecutedFormula must return the single value as a string',
        );
    }

    #[Test]
    public function stringifiesBooleanValue(): void
    {
        self::assertThat(
            new ExecutedFormula(
                new FakeActions([
                    new FakeAction([true]),
                ]),
            ),
            new HasFormulaResult('1'),
            'ExecutedFormula must stringify a boolean value to its integer string representation',
        );
    }

    #[Test]
    public function throwsWhenFormulaDoesNotReduceToSingleValue(): void
    {
        $this->expectException(SheriffException::class);

        (new ExecutedFormula(
            new FakeActions([
                new FakeAction(['a', 'b']),
            ]),
        ))->result();
    }
}
