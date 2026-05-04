<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config\Dirs;

use Haspadar\Sheriff\Config\Dirs\GlobDirs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GlobDirsTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenNoDirs(): void
    {
        self::assertSame(
            [],
            (new GlobDirs([]))->toList(),
            'GlobDirs must return an empty list when constructed with no directories',
        );
    }

    #[Test]
    public function suffixesEachDirWithGlobWildcard(): void
    {
        self::assertSame(
            ['vendor/*', 'node_modules/*'],
            (new GlobDirs(['vendor', 'node_modules']))->toList(),
            'GlobDirs must suffix each directory with /*',
        );
    }
}
