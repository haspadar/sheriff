<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Map;

use Haspadar\Sheriff\Chain\Map\EachFormatted;
use Haspadar\Sheriff\Chain\Map\Formatted;
use Haspadar\Sheriff\Chain\Plain\ListText;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EachFormattedTest extends TestCase
{
    #[Test]
    public function wrapsEachPartIntoFormatted(): void
    {
        self::assertContainsOnlyInstancesOf(
            Formatted::class,
            (new EachFormatted(
                new ListText(new ListValue([
                    new StringValue('src'),
                    new StringValue('tests'),
                ])),
                '- %s',
            ))->parts(),
            'EachFormatted must wrap every part of the source list into Formatted',
        );
    }

    #[Test]
    public function preservesPartOrderAndCountWhenWrapping(): void
    {
        self::assertSame(
            ['- src', '- tests', '- docs'],
            array_map(
                static fn(object $part): string => $part->rendered(),
                (new EachFormatted(
                    new ListText(new ListValue([
                        new StringValue('src'),
                        new StringValue('tests'),
                        new StringValue('docs'),
                    ])),
                    '- %s',
                ))->parts(),
            ),
            'EachFormatted must keep the order and count of source parts intact',
        );
    }

    #[Test]
    public function appliesTemplateToEachRenderedPart(): void
    {
        $parts = (new EachFormatted(
            new ListText(new ListValue([
                new StringValue('src'),
                new StringValue('tests'),
            ])),
            '- %s',
        ))->parts();

        self::assertSame(
            ['- src', '- tests'],
            [$parts[0]->rendered(), $parts[1]->rendered()],
            'EachFormatted must apply the sprintf template to every part',
        );
    }

    #[Test]
    public function returnsEmptyPartsForEmptySourceList(): void
    {
        self::assertSame(
            [],
            (new EachFormatted(
                new ListText(new ListValue([])),
                '- %s',
            ))->parts(),
            'EachFormatted must return no parts when the source list is empty',
        );
    }

    #[Test]
    public function refusesDirectRendering(): void
    {
        $this->expectException(SheriffException::class);

        (new EachFormatted(
            new ListText(new ListValue([new StringValue('src')])),
            '- %s',
        ))->rendered();
    }

    #[Test]
    public function composesNestedEachFormattedWrappingPartsTwice(): void
    {
        $parts = (new EachFormatted(
            new EachFormatted(
                new ListText(new ListValue([new StringValue('src')])),
                'inner: %s',
            ),
            'outer: %s',
        ))->parts();

        self::assertSame(
            'outer: inner: src',
            $parts[0]->rendered(),
            'EachFormatted must stay Listed so it can be wrapped by another EachFormatted',
        );
    }

    #[Test]
    public function appliesTemplateWithoutPlaceholderAsLiteralPerPart(): void
    {
        $parts = (new EachFormatted(
            new ListText(new ListValue([
                new StringValue('src'),
                new StringValue('tests'),
            ])),
            'literal',
        ))->parts();

        self::assertSame(
            ['literal', 'literal'],
            [$parts[0]->rendered(), $parts[1]->rendered()],
            'EachFormatted must accept a template without %s, mirroring Formatted contract',
        );
    }
}
