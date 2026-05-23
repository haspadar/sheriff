<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Map;

use Haspadar\Sheriff\Chain\Map\EachReplaced;
use Haspadar\Sheriff\Chain\Map\Replaced;
use Haspadar\Sheriff\Chain\Plain\ListText;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EachReplacedTest extends TestCase
{
    #[Test]
    public function wrapsEachPartIntoReplaced(): void
    {
        self::assertContainsOnlyInstancesOf(
            Replaced::class,
            (new EachReplaced(
                new ListText(new ListValue([
                    new StringValue('8.3'),
                    new StringValue('8.4'),
                ])),
                '.',
                'x',
            ))->parts(),
            'EachReplaced must wrap every part of the source list into Replaced',
        );
    }

    #[Test]
    public function rewritesEachRenderedPart(): void
    {
        $parts = (new EachReplaced(
            new ListText(new ListValue([
                new StringValue('8.3'),
                new StringValue('8.4'),
            ])),
            '.',
            'x',
        ))->parts();

        self::assertSame(
            ['8x3', '8x4'],
            [$parts[0]->rendered(), $parts[1]->rendered()],
            'EachReplaced must rewrite every needle in every rendered part',
        );
    }

    #[Test]
    public function returnsEmptyPartsForEmptySourceList(): void
    {
        self::assertSame(
            [],
            (new EachReplaced(
                new ListText(new ListValue([])),
                '.',
                'x',
            ))->parts(),
            'EachReplaced must return no parts when the source list is empty',
        );
    }

    #[Test]
    public function refusesDirectRendering(): void
    {
        $this->expectException(SheriffException::class);

        (new EachReplaced(
            new ListText(new ListValue([new StringValue('8.3')])),
            '.',
            'x',
        ))->rendered();
    }
}
