<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Map;

use Haspadar\Sheriff\Chain\Map\Replaced;
use Haspadar\Sheriff\Chain\Plain\StringText;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReplacedTest extends TestCase
{
    #[Test]
    public function replacesEveryNeedleOccurrence(): void
    {
        self::assertSame(
            '8x3',
            (new Replaced(new StringText(new StringValue('8.3')), '.', 'x'))->rendered(),
            'Replaced must substitute every needle occurrence with the replacement',
        );
    }

    #[Test]
    public function returnsRenderedAsIsWhenNeedleAbsent(): void
    {
        self::assertSame(
            'pure',
            (new Replaced(new StringText(new StringValue('pure')), '.', 'x'))->rendered(),
            'Replaced must return the rendered output unchanged when the needle is absent',
        );
    }
}
