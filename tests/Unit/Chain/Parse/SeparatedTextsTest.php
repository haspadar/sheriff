<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Parse;

use Haspadar\Sheriff\Chain\Parse\SeparatedTexts;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeparatedTextsTest extends TestCase
{
    #[Test]
    public function trimsFragmentsAroundSeparator(): void
    {
        self::assertSame(
            ['A()', 'B()'],
            (new SeparatedTexts(' A() | B() ', '|'))->values(),
            'SeparatedTexts must trim fragments split around a separator',
        );
    }

    #[Test]
    public function keepsEscapedQuotesInsideQuotedFragments(): void
    {
        self::assertSame(
            ['A("x\"|y")', 'B()'],
            (new SeparatedTexts('A("x\"|y")|B()', '|'))->values(),
            'SeparatedTexts must ignore separators inside escaped quoted fragments',
        );
    }
}
