<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonString;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeonStringTest extends TestCase
{
    #[Test]
    public function rendersStringAsDoubleQuotedLiteral(): void
    {
        self::assertSame(
            '"1G"',
            (new NeonString(new StringValue('1G')))->rendered(),
            'NeonString must render the string payload wrapped in double quotes',
        );
    }

    #[Test]
    public function escapesEmbeddedDoubleQuote(): void
    {
        self::assertSame(
            '"a\\"b"',
            (new NeonString(new StringValue('a"b')))->rendered(),
            'NeonString must backslash-escape an embedded double quote',
        );
    }

    #[Test]
    public function escapesEmbeddedBackslash(): void
    {
        self::assertSame(
            '"a\\\\b"',
            (new NeonString(new StringValue('a\\b')))->rendered(),
            'NeonString must backslash-escape an embedded backslash',
        );
    }

    #[Test]
    public function escapesEmbeddedNewline(): void
    {
        self::assertSame(
            '"a\\nb"',
            (new NeonString(new StringValue("a\nb")))->rendered(),
            'NeonString must escape newline so the literal stays on a single line of valid neon',
        );
    }

    #[Test]
    public function escapesEmbeddedTab(): void
    {
        self::assertSame(
            '"a\\tb"',
            (new NeonString(new StringValue("a\tb")))->rendered(),
            'NeonString must escape tab so the literal stays well-formed neon',
        );
    }
}
