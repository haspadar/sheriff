<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Files;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Files\FolderFiles;
use Haspadar\Sheriff\Storage\InMemoryStorage;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFiles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FolderFilesTest extends TestCase
{
    #[Test]
    public function exposesFilesFromStorageEntries(): void
    {
        self::assertThat(
            new FolderFiles(
                new InMemoryStorage([
                    'a/one.txt' => new TextFile('a/one.txt', '1'),
                    'a/two.txt' => new TextFile('a/two.txt', '2'),
                ]),
                'a',
            ),
            new HasFiles([
                'a/one.txt' => '1',
                'a/two.txt' => '2',
            ]),
            'FolderFiles must expose all files from the given storage folder',
        );
    }
}
