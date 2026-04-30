<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Unit\Chain\Parse;

use Haspadar\Piqule\Chain\Parse\SourceFormula;
use Haspadar\Piqule\Chain\Plain\BoolText;
use Haspadar\Piqule\Chain\Plain\IntText;
use Haspadar\Piqule\Chain\Plain\ListText;
use Haspadar\Piqule\Chain\Render\Neon\NeonTree;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Settings;
use Haspadar\Piqule\Settings\Value\BoolValue;
use Haspadar\Piqule\Settings\Value\IntValue;
use Haspadar\Piqule\Settings\Value\ListValue;
use Haspadar\Piqule\Settings\Value\StringValue;
use Haspadar\Piqule\Settings\Value\TreeValue;
use Haspadar\Piqule\Settings\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class SourceFormulaTest extends TestCase
{
    #[Test]
    public function buildsIntTextFromIntegerSetting(): void
    {
        self::assertSame(
            '9',
            (new SourceFormula(IntText::class, ['phpstan.level']))
                ->op([], self::settings(['phpstan.level' => new IntValue(9)]))
                ->rendered(),
            'SourceFormula must build the source op with the value resolved from the settings key',
        );
    }

    #[Test]
    public function buildsBoolTextFromBooleanSetting(): void
    {
        self::assertSame(
            'true',
            (new SourceFormula(BoolText::class, ['phpstan.cli']))
                ->op([], self::settings(['phpstan.cli' => new BoolValue(true)]))
                ->rendered(),
            'SourceFormula must dispatch BoolValue settings to the BoolText source',
        );
    }

    #[Test]
    public function buildsNeonTreeFromTreeSettingForFormatRenderers(): void
    {
        self::assertInstanceOf(
            NeonTree::class,
            (new SourceFormula(NeonTree::class, ['phpstan.parameters']))
                ->op([], self::settings(['phpstan.parameters' => new TreeValue([])])),
            'SourceFormula must work for Chain\\Render\\* classes that take a single Value, not just Plain',
        );
    }

    #[Test]
    public function buildsListedSourceWhenTargetClassImplementsListed(): void
    {
        self::assertInstanceOf(
            ListText::class,
            (new SourceFormula(ListText::class, ['phpstan.paths']))
                ->op([], self::settings([
                    'phpstan.paths' => new ListValue([new StringValue('src')]),
                ])),
            'SourceFormula must instantiate Listed source classes such as ListText',
        );
    }

    #[Test]
    public function ignoresPreviousPipelineStagesForSources(): void
    {
        self::assertSame(
            '9',
            (new SourceFormula(IntText::class, ['phpstan.level']))
                ->op(
                    [new IntText(new IntValue(123))],
                    self::settings(['phpstan.level' => new IntValue(9)]),
                )
                ->rendered(),
            'SourceFormula must ignore the previous pipeline stages — sources read from settings only',
        );
    }

    #[Test]
    public function failsWhenArgumentCountIsNotExactlyOne(): void
    {
        $this->expectException(PiquleException::class);

        (new SourceFormula(IntText::class, []))
            ->op([], self::settings([]));
    }

    #[Test]
    public function failsWithMultipleArguments(): void
    {
        $this->expectException(PiquleException::class);

        (new SourceFormula(IntText::class, ['phpstan.level', 'extra']))
            ->op([], self::settings(['phpstan.level' => new IntValue(9)]));
    }

    #[Test]
    public function failsWhenSettingsKeyIsAbsent(): void
    {
        $this->expectException(PiquleException::class);

        (new SourceFormula(IntText::class, ['phpstan.unknown']))
            ->op([], self::settings([]));
    }

    #[Test]
    public function surfacesTypeMismatchAsPhpTypeError(): void
    {
        $this->expectException(TypeError::class);

        (new SourceFormula(IntText::class, ['phpstan.cli']))
            ->op([], self::settings(['phpstan.cli' => new BoolValue(true)]));
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
