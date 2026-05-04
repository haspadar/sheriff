<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Files;

use Haspadar\Sheriff\Files\FolderFiles;
use Haspadar\Sheriff\Storage\DiskStorage;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFiles;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FolderFilesTest extends TestCase
{
    #[Test]
    public function exposesOnlyFilesFromGivenFolder(): void
    {
        self::assertThat(
            new FolderFiles(
                new DiskStorage(
                    (new TempFolder())
                        ->withFile('a/one.txt', '1')
                        ->withFile('a/two.txt', '2')
                        ->withFile('b/skip.txt', 'x')
                        ->path(),
                ),
                'a',
            ),
            new HasFiles([
                'a/one.txt' => '1',
                'a/two.txt' => '2',
            ]),
            'FolderFiles must expose only files from the specified folder, ignoring files in other folders',
        );
    }
}
