<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config\Dirs;

use Haspadar\Sheriff\Config\Dirs\ProjectDirs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProjectDirsTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenNoDirs(): void
    {
        self::assertSame(
            [],
            (new ProjectDirs([]))->toList(),
            'ProjectDirs must return an empty list when constructed with no directories',
        );
    }

    #[Test]
    public function prefixesEachDirWithRelativePath(): void
    {
        self::assertSame(
            ['../../src', '../../tests'],
            (new ProjectDirs(['src', 'tests']))->toList(),
            'ProjectDirs must prefix each directory with ../../',
        );
    }
}
