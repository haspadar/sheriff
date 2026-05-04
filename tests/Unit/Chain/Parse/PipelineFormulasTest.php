<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Parse;

use Haspadar\Sheriff\Chain\Parse\PipelineFormulas;
use Haspadar\Sheriff\Chain\Parse\PipelineOp;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PipelineFormulasTest extends TestCase
{
    #[Test]
    public function parsesSingleSourceFormula(): void
    {
        self::assertSame(
            '9',
            (new PipelineOp(
                (new PipelineFormulas('IntText(phpstan.level)'))->formulas(),
                self::settings(['phpstan.level' => new IntValue(9)]),
            ))->rendered(),
            'PipelineFormulas must parse one source formula into an executable pipeline',
        );
    }

    #[Test]
    public function parsesSourceMapAndReducePipeline(): void
    {
        self::assertSame(
            "- src\n- tests",
            (new PipelineOp(
                (new PipelineFormulas('ListText(phpstan.paths)|EachFormatted("- %s")|Joined("\n")'))->formulas(),
                self::settings([
                    'phpstan.paths' => new ListValue([
                        new StringValue('src'),
                        new StringValue('tests'),
                    ]),
                ]),
            ))->rendered(),
            'PipelineFormulas must preserve pipeline stage order from source through map to reduce',
        );
    }

    #[Test]
    public function keepsSeparatorsInsideQuotedArguments(): void
    {
        self::assertSame(
            'x,Sheriff|y',
            (new PipelineOp(
                (new PipelineFormulas('StringText(app.name)|Formatted("x,%s|y")'))->formulas(),
                self::settings(['app.name' => new StringValue('Sheriff')]),
            ))->rendered(),
            'PipelineFormulas must not split comma or pipe characters inside quoted arguments',
        );
    }

    #[Test]
    public function failsWhenFormulaNameIsUnknown(): void
    {
        $this->expectException(SheriffException::class);

        (new PipelineFormulas('MissingFormula(app.name)'))->formulas();
    }

    #[Test]
    public function failsWhenFormulaSyntaxIsInvalid(): void
    {
        $this->expectException(SheriffException::class);

        (new PipelineFormulas('StringText'))->formulas();
    }

    /**
     * @param array<string, Value> $values
     */
    private static function settings(array $values): Settings
    {
        return new readonly class ($values) implements Settings {
            /**
             * @param array<string, Value> $values
             */
            public function __construct(private array $values) {}

            #[Override]
            public function has(string $name): bool
            {
                return array_key_exists($name, $this->values);
            }

            #[Override]
            public function value(string $name): Value
            {
                return $this->values[$name];
            }
        };
    }
}
