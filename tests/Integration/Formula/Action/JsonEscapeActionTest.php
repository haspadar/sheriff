<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Formula\Action;

use Haspadar\Sheriff\Formula\Action\JsonEscapeAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonEscapeActionTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function payloads(): iterable
    {
        yield 'plain value' => ['hello'];
        yield 'value with spaces' => ['hello world'];
        yield 'value with double quotes' => ['say "hi"'];
        yield 'value with backslash' => ['a\\b'];
        yield 'value with newline' => ["line1\nline2"];
        yield 'value with tab' => ["a\tb"];
        yield 'value with control char' => ["bell\x07here"];
        yield 'non-ASCII value' => ['привет'];
        yield 'line separator' => ["a\u{2028}b"];
        yield 'paragraph separator' => ["a\u{2029}b"];
        yield 'forward slash' => ['path/to/file'];
        yield 'empty string' => [''];
    }

    #[Test]
    #[DataProvider('payloads')]
    public function roundTripsThroughJsonDecoder(string $original): void
    {
        $escaped = (new JsonEscapeAction())
            ->transformed(new ListArgs([$original]))
            ->values()[0];

        $decoded = json_decode(sprintf('"%s"', $escaped), false, 512, JSON_THROW_ON_ERROR);

        if (!is_string($decoded)) {
            throw new RuntimeException('Decoded JSON value must be a string');
        }

        self::assertSame(
            $original,
            $decoded,
            'Escaped value wrapped in double quotes must json_decode back to the original',
        );
    }
}
