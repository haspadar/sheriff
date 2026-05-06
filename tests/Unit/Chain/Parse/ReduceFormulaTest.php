<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Parse;

use Haspadar\Sheriff\Chain\Map\EachFormatted;
use Haspadar\Sheriff\Chain\Parse\ReduceFormula;
use Haspadar\Sheriff\Chain\Plain\IntText;
use Haspadar\Sheriff\Chain\Plain\ListText;
use Haspadar\Sheriff\Chain\Reduce\Joined;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReduceFormulaTest extends TestCase
{
    #[Test]
    public function unwrapsListedPreviousIntoJoinedParts(): void
    {
        self::assertSame(
            "src\ntests",
            (new ReduceFormula(Joined::class, ["\n"]))
                ->op(
                    [new ListText(new ListValue([
                        new StringValue('src'),
                        new StringValue('tests'),
                    ]))],
                    self::settings(),
                )
                ->rendered(),
            'ReduceFormula must unwrap a single Listed previous stage via parts() before passing to Joined',
        );
    }

    #[Test]
    public function joinsMultipleNonListedPreviousOpsAsIs(): void
    {
        self::assertSame(
            '1, 2',
            (new ReduceFormula(Joined::class, [', ']))
                ->op(
                    [
                        new IntText(new IntValue(1)),
                        new IntText(new IntValue(2)),
                    ],
                    self::settings(),
                )
                ->rendered(),
            'ReduceFormula must pass the previous op list straight through when the head is not Listed',
        );
    }

    #[Test]
    public function unwrapsEachFormattedListedBeforeJoining(): void
    {
        self::assertSame(
            "- src\n- tests",
            (new ReduceFormula(Joined::class, ["\n"]))
                ->op(
                    [new EachFormatted(
                        new ListText(new ListValue([
                            new StringValue('src'),
                            new StringValue('tests'),
                        ])),
                        '- %s',
                    )],
                    self::settings(),
                )
                ->rendered(),
            'ReduceFormula must unwrap any Listed head, not just plain ListText',
        );
    }

    #[Test]
    public function passesSingleNonListedPreviousThroughAsOneElementList(): void
    {
        self::assertSame(
            '7',
            (new ReduceFormula(Joined::class, [', ']))
                ->op([new IntText(new IntValue(7))], self::settings())
                ->rendered(),
            'ReduceFormula must keep a single non-Listed previous as a one-element list, not unwrap it',
        );
    }

    #[Test]
    public function failsWhenPreviousPipelineStagesAreEmpty(): void
    {
        $this->expectException(SheriffException::class);

        (new ReduceFormula(Joined::class, ["\n"]))
            ->op([], self::settings());
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

            #[Override]
            public function keys(): array
            {
                return [];
            }
        };
    }
}
