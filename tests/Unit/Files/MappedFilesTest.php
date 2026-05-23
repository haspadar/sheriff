<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Files;

use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\File\ReplacedFile;
use Haspadar\Sheriff\Files\MappedFiles;
use Haspadar\Sheriff\Files\TextFiles;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFiles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MappedFilesTest extends TestCase
{
    #[Test]
    public function mapsAllFiles(): void
    {
        self::assertThat(
            new MappedFiles(
                new TextFiles([
                    'README.md' => 'Hello, {{name}}',
                    'config/app.ini' => 'name={{name}}',
                ]),
                static fn(File $file) => new ReplacedFile(
                    $file,
                    '{{name}}',
                    'Sheriff',
                ),
            ),
            new HasFiles([
                'README.md' => 'Hello, Sheriff',
                'config/app.ini' => 'name=Sheriff',
            ]),
            'MappedFiles must apply the mapping function to every file in the source',
        );
    }
}
