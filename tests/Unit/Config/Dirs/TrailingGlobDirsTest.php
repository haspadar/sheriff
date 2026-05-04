<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config\Dirs;

use Haspadar\Sheriff\Config\Dirs\TrailingGlobDirs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TrailingGlobDirsTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenNoDirs(): void
    {
        self::assertSame(
            [],
            (new TrailingGlobDirs([]))->toList(),
            'TrailingGlobDirs must return an empty list when constructed with no directories',
        );
    }

    #[Test]
    public function suffixesEachDirWithRecursiveGlob(): void
    {
        self::assertSame(
            ['vendor/**', '.git/**'],
            (new TrailingGlobDirs(['vendor', '.git']))->toList(),
            'TrailingGlobDirs must suffix each directory with /**',
        );
    }
}
