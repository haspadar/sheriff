<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config\Dirs;

use Haspadar\Sheriff\Config\Dirs\NegatedGlobDirs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NegatedGlobDirsTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenNoDirs(): void
    {
        self::assertSame(
            [],
            (new NegatedGlobDirs([]))->toList(),
            'NegatedGlobDirs must return an empty list when constructed with no directories',
        );
    }

    #[Test]
    public function prefixesEachDirWithNegationAndSuffixesWithGlob(): void
    {
        self::assertSame(
            ['!vendor/**', '!.git/**'],
            (new NegatedGlobDirs(['vendor', '.git']))->toList(),
            'NegatedGlobDirs must prefix each directory with ! and suffix with /**',
        );
    }
}
