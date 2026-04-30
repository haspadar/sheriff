<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Unit\Chain\Parse;

use Haspadar\Piqule\Chain\Map\EachFormatted;
use Haspadar\Piqule\Chain\Parse\MapFormula;
use Haspadar\Piqule\Chain\Parse\PipelineOp;
use Haspadar\Piqule\Chain\Parse\ReduceFormula;
use Haspadar\Piqule\Chain\Parse\SourceFormula;
use Haspadar\Piqule\Chain\Plain\IntText;
use Haspadar\Piqule\Chain\Plain\ListText;
use Haspadar\Piqule\Chain\Reduce\Joined;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Settings;
use Haspadar\Piqule\Settings\Value\IntValue;
use Haspadar\Piqule\Settings\Value\ListValue;
use Haspadar\Piqule\Settings\Value\StringValue;
use Haspadar\Piqule\Settings\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PipelineOpTest extends TestCase
{
    #[Test]
    public function rendersSingleSourceFormulaPipeline(): void
    {
        self::assertSame(
            '9',
            (new PipelineOp(
                [new SourceFormula(IntText::class, ['phpstan.level'])],
                self::settings(['phpstan.level' => new IntValue(9)]),
            ))->rendered(),
            'PipelineOp must render a single-formula pipeline by delegating to that source op',
        );
    }

    #[Test]
    public function chainsSourceMapAndReduceIntoFinalString(): void
    {
        self::assertSame(
            "- src\n- tests",
            (new PipelineOp(
                [
                    new SourceFormula(ListText::class, ['phpstan.paths']),
                    new MapFormula(EachFormatted::class, ['- %s']),
                    new ReduceFormula(Joined::class, ["\n"]),
                ],
                self::settings([
                    'phpstan.paths' => new ListValue([
                        new StringValue('src'),
                        new StringValue('tests'),
                    ]),
                ]),
            ))->rendered(),
            'PipelineOp must feed each formula the previous op and render the tail',
        );
    }

    #[Test]
    public function chainsSourceAndReduceWithoutMapStage(): void
    {
        self::assertSame(
            'src, tests',
            (new PipelineOp(
                [
                    new SourceFormula(ListText::class, ['phpstan.paths']),
                    new ReduceFormula(Joined::class, [', ']),
                ],
                self::settings([
                    'phpstan.paths' => new ListValue([
                        new StringValue('src'),
                        new StringValue('tests'),
                    ]),
                ]),
            ))->rendered(),
            'PipelineOp must compose head-to-tail even without a middle decorator',
        );
    }

    #[Test]
    public function failsOnEmptyFormulaList(): void
    {
        $this->expectException(PiquleException::class);

        (new PipelineOp([], self::settings([])))->rendered();
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
