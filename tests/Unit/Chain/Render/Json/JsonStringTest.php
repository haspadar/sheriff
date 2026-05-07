<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Render\Json\JsonString;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonStringTest extends TestCase
{
    #[Test]
    public function rendersPlainStringInDoubleQuotes(): void
    {
        self::assertSame(
            '"json5"',
            (new JsonString(new StringValue('json5')))->rendered(),
            'JsonString must render the payload as a quoted json literal',
        );
    }

    #[Test]
    public function escapesEmbeddedDoubleQuote(): void
    {
        self::assertSame(
            '"a\"b"',
            (new JsonString(new StringValue('a"b')))->rendered(),
            'JsonString must backslash-escape an embedded double quote',
        );
    }

    #[Test]
    public function keepsForwardSlashUnescaped(): void
    {
        self::assertSame(
            '"a/b"',
            (new JsonString(new StringValue('a/b')))->rendered(),
            'JsonString must keep forward slashes unescaped to preserve readable paths',
        );
    }

    #[Test]
    public function escapesEmbeddedNewline(): void
    {
        self::assertSame(
            '"a\nb"',
            (new JsonString(new StringValue("a\nb")))->rendered(),
            'JsonString must escape control characters so the literal stays single-line valid json',
        );
    }
}
