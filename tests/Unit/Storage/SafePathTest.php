<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Storage;

use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Storage\SafePath;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SafePathTest extends TestCase
{
    #[Test]
    public function normalizesBackslashesToForwardSlashes(): void
    {
        self::assertSame(
            '/root/a/b/c',
            (new SafePath('/root'))->resolve('a\\b\\c'),
            'SafePath must normalize backslashes to forward slashes',
        );
    }

    #[Test]
    public function stripsTrailingSlashFromRoot(): void
    {
        self::assertSame(
            '/root/file.txt',
            (new SafePath('/root/'))->resolve('file.txt'),
            'SafePath must strip trailing slash from root before joining',
        );
    }

    #[Test]
    public function resolvesSimplePath(): void
    {
        self::assertSame(
            '/root/dir/file.txt',
            (new SafePath('/root'))->resolve('dir/file.txt'),
            'SafePath must join root with relative location',
        );
    }

    #[Test]
    public function skipsDotSegments(): void
    {
        self::assertSame(
            '/root/a/b',
            (new SafePath('/root'))->resolve('a/./b'),
            'SafePath must skip dot segments in location',
        );
    }

    #[Test]
    public function resolvesDotDotWithinBounds(): void
    {
        self::assertSame(
            '/root/b',
            (new SafePath('/root'))->resolve('a/../b'),
            'SafePath must resolve .. by popping the previous segment',
        );
    }

    #[Test]
    public function throwsWhenDotDotEscapesRoot(): void
    {
        $this->expectException(SheriffException::class);

        (new SafePath('/root'))->resolve('../etc/passwd');
    }

    #[Test]
    public function skipsEmptySegments(): void
    {
        self::assertSame(
            '/root/a/b',
            (new SafePath('/root'))->resolve('a//b'),
            'SafePath must skip empty segments from consecutive slashes',
        );
    }
}
