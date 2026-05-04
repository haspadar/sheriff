<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula;

use Haspadar\Sheriff\Formula\NormalizedFormula;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NormalizedFormulaTest extends TestCase
{
    #[Test]
    public function collapsesMultilineExpression(): void
    {
        $raw = <<<'FORMULA'
            config(a)

            |   default(["x"])

            |   format("%s")
            FORMULA;

        self::assertSame(
            'config(a)|default(["x"])|format("%s")',
            (new NormalizedFormula($raw))->result(),
            'NormalizedFormula must collapse a multiline expression into a single line without extra whitespace',
        );
    }

    #[Test]
    public function normalizesPipeSpacingWhenSpacesAroundPipeArePresent(): void
    {
        self::assertSame(
            'config(a)|join(",")',
            (new NormalizedFormula(
                'config(a)   |   join(",")',
            ))->result(),
            'NormalizedFormula must remove whitespace around pipe separators',
        );
    }

    #[Test]
    public function trimsOuterWhitespaceWhenExpressionHasSurroundingSpaces(): void
    {
        self::assertSame(
            'config(a)|format("%s")',
            (new NormalizedFormula(
                '   config(a)|format("%s")   ',
            ))->result(),
            'NormalizedFormula must trim leading and trailing whitespace from the expression',
        );
    }

    #[Test]
    public function preservesJsonLiteral(): void
    {
        self::assertSame(
            'config(a)|default([1])|format("%s")',
            (new NormalizedFormula(
                'config(a) | default([1]) | format("%s")',
            ))->result(),
            'NormalizedFormula must preserve JSON literals while normalizing pipe spacing',
        );
    }
}
