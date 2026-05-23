<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonBareString;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeonBareStringTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function safeStrings(): iterable
    {
        yield 'identifier' => ['table', 'table'];
        yield 'leading-backslash class' => ['\\Throwable', '\\Throwable'];
        yield 'fully qualified class' => ['\\Haspadar\\Sheriff\\SheriffException', '\\Haspadar\\Sheriff\\SheriffException'];
        yield 'dotted path' => ['1G.alpha', '1G.alpha'];
    }

    #[DataProvider('safeStrings')]
    #[Test]
    public function rendersSafePayloadWithoutQuotes(string $raw, string $expected): void
    {
        self::assertSame(
            $expected,
            (new NeonBareString(new StringValue($raw)))->rendered(),
            'NeonBareString must emit safe payloads without surrounding quotes',
        );
    }

    /** @return iterable<string, array{string, string}> */
    public static function unsafeStrings(): iterable
    {
        yield 'whitespace' => ['error format', '"error format"'];
        yield 'colon' => ['key:value', '"key:value"'];
        yield 'pure number' => ['42', '"42"'];
        yield 'reserved literal' => ['true', '"true"'];
        yield 'leading dash' => ['-flag', '"-flag"'];
        yield 'embedded quote' => ['a"b', '"a\\"b"'];
        yield 'empty' => ['', '""'];
    }

    #[DataProvider('unsafeStrings')]
    #[Test]
    public function fallsBackToQuotedFormForUnsafePayload(string $raw, string $expected): void
    {
        self::assertSame(
            $expected,
            (new NeonBareString(new StringValue($raw)))->rendered(),
            'NeonBareString must fall back to NeonString quoting when the payload is not safe to emit bare',
        );
    }
}
