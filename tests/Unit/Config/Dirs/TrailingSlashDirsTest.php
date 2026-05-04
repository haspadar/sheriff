<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config\Dirs;

use Haspadar\Sheriff\Config\Dirs\TrailingSlashDirs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TrailingSlashDirsTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenNoDirs(): void
    {
        self::assertSame(
            [],
            (new TrailingSlashDirs([]))->toList(),
            'TrailingSlashDirs must return an empty list when constructed with no directories',
        );
    }

    #[Test]
    public function suffixesEachDirWithTrailingSlash(): void
    {
        self::assertSame(
            ['vendor/', 'node_modules/', '.git/'],
            (new TrailingSlashDirs(['vendor', 'node_modules', '.git']))->toList(),
            'TrailingSlashDirs must suffix each directory with /',
        );
    }
}
