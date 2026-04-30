<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Unit\Chain\Parse;

use Haspadar\Piqule\Chain\Map\EachFormatted;
use Haspadar\Piqule\Chain\Map\Formatted;
use Haspadar\Piqule\Chain\Parse\MapFormula;
use Haspadar\Piqule\Chain\Plain\IntText;
use Haspadar\Piqule\Chain\Plain\ListText;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Settings;
use Haspadar\Piqule\Settings\Value\IntValue;
use Haspadar\Piqule\Settings\Value\ListValue;
use Haspadar\Piqule\Settings\Value\StringValue;
use Haspadar\Piqule\Settings\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class MapFormulaTest extends TestCase
{
    #[Test]
    public function wrapsPreviousOpIntoFormatted(): void
    {
        self::assertSame(
            '- 9',
            (new MapFormula(Formatted::class, ['- %s']))
                ->op([new IntText(new IntValue(9))], self::settings())
                ->rendered(),
            'MapFormula must feed the previous op as the first constructor argument and pass literal args through',
        );
    }

    #[Test]
    public function wrapsListedSourceIntoEachFormatted(): void
    {
        self::assertInstanceOf(
            EachFormatted::class,
            (new MapFormula(EachFormatted::class, ['- %s']))
                ->op(
                    [new ListText(new ListValue([new StringValue('src')]))],
                    self::settings(),
                ),
            'MapFormula must work for any Chain\\Map decorator, including those expecting a Listed input',
        );
    }

    #[Test]
    public function failsWhenPreviousPipelineStagesAreEmpty(): void
    {
        $this->expectException(PiquleException::class);

        (new MapFormula(Formatted::class, ['- %s']))
            ->op([], self::settings());
    }

    #[Test]
    public function failsWhenPreviousHasMoreThanOneOp(): void
    {
        $this->expectException(PiquleException::class);

        (new MapFormula(Formatted::class, ['- %s']))
            ->op(
                [
                    new IntText(new IntValue(1)),
                    new IntText(new IntValue(2)),
                ],
                self::settings(),
            );
    }

    #[Test]
    public function surfacesIncompatiblePreviousOpAsPhpTypeError(): void
    {
        $this->expectException(TypeError::class);

        (new MapFormula(EachFormatted::class, ['- %s']))
            ->op([new IntText(new IntValue(9))], self::settings());
    }

    private static function settings(): Settings
    {
        return new readonly class () implements Settings {
            #[Override]
            public function has(string $name): bool
            {
                return false;
            }

            #[Override]
            public function value(string $name): Value
            {
                throw new \LogicException('not used');
            }
        };
    }
}
