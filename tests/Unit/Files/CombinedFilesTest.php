<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Files;

use Haspadar\Sheriff\Files\CombinedFiles;
use Haspadar\Sheriff\Files\TextFiles;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFiles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CombinedFilesTest extends TestCase
{
    #[Test]
    public function combinesMultipleFileSources(): void
    {
        self::assertThat(
            new CombinedFiles([
                new TextFiles([
                    'README.md' => 'Sheriff',
                ]),
                new TextFiles([
                    'config/app.ini' => 'name=sheriff',
                ]),
            ]),
            new HasFiles([
                'README.md' => 'Sheriff',
                'config/app.ini' => 'name=sheriff',
            ]),
            'CombinedFiles must expose all files from all provided sources',
        );
    }
}
