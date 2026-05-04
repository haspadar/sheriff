<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Plain;

use Haspadar\Sheriff\Chain\Plain\StringText;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StringTextTest extends TestCase
{
    #[Test]
    public function rendersStringPayloadVerbatim(): void
    {
        self::assertSame(
            '1G',
            (new StringText(new StringValue('1G')))->rendered(),
            'StringText must render the string payload as-is, without quoting',
        );
    }

    #[Test]
    public function preservesSpecialCharactersWithoutEscaping(): void
    {
        self::assertSame(
            'a"b\\c',
            (new StringText(new StringValue('a"b\\c')))->rendered(),
            'StringText must not escape quotes or backslashes — escaping is the format renderer responsibility',
        );
    }

    #[Test]
    public function rendersEmptyStringAsEmptyOutput(): void
    {
        self::assertSame(
            '',
            (new StringText(new StringValue('')))->rendered(),
            'StringText must render an empty StringValue as an empty string',
        );
    }

    #[Test]
    public function preservesNewlinesAndTabsWithoutEscaping(): void
    {
        self::assertSame(
            "line1\nline2\tend",
            (new StringText(new StringValue("line1\nline2\tend")))->rendered(),
            'StringText must keep newlines and tabs verbatim — escaping is the format renderer responsibility',
        );
    }
}
