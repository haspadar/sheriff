<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Storage;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Storage\DiskStorage;
use Haspadar\Sheriff\Tests\Constraint\Storage\HasEntries;
use Haspadar\Sheriff\Tests\Constraint\Storage\HasEntry;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DiskStorageTest extends TestCase
{
    #[Test]
    public function writesAndReadsFile(): void
    {
        self::assertThat(
            (new DiskStorage(
                (new TempFolder())->path(),
            ))->write(
                new TextFile('a/b/read.txt', 'hello'),
            ),
            new HasEntry('a/b/read.txt', 'hello'),
            'DiskStorage must write a file and make its contents readable',
        );
    }

    #[Test]
    public function overwritesExistingFileWhenWritingToSamePath(): void
    {
        self::assertThat(
            (new DiskStorage(
                (new TempFolder())
                    ->withFile('overwrite.txt', 'first')
                    ->path(),
            ))->write(
                new TextFile('overwrite.txt', 'second'),
            ),
            new HasEntry('overwrite.txt', 'second'),
            'DiskStorage must overwrite an existing file with new contents',
        );
    }

    #[Test]
    public function reportsExistingLocation(): void
    {
        self::assertTrue(
            (new DiskStorage(
                (new TempFolder())
                    ->withFile('exists/file.txt', 'data')
                    ->path(),
            ))->exists('exists/file.txt'),
            'DiskStorage must report existing location',
        );
    }

    #[Test]
    public function reportsNonExistingLocation(): void
    {
        self::assertFalse(
            (new DiskStorage(
                (new TempFolder())->path(),
            ))->exists('missing/file.txt'),
            'DiskStorage must report non-existing location',
        );
    }

    #[Test]
    public function throwsWhenReadingMissingLocation(): void
    {
        $this->expectException(SheriffException::class);

        (new DiskStorage(
            (new TempFolder())->path(),
        ))->read('no/such/file.txt');
    }

    #[Test]
    public function listsEntriesInFolder(): void
    {
        self::assertThat(
            new DiskStorage(
                (new TempFolder())
                    ->withFile('a/one.txt', '1')
                    ->withFile('a/two.txt', '2')
                    ->path(),
            ),
            new HasEntries('a', ['a/one.txt', 'a/two.txt']),
            'DiskStorage must list all entries in the specified folder',
        );
    }

    #[Test]
    public function listsEntriesFromRootDirectory(): void
    {
        self::assertThat(
            new DiskStorage(
                (new TempFolder())
                    ->withFile('root-file.txt', 'data')
                    ->path(),
            ),
            new HasEntries('', ['root-file.txt']),
            'DiskStorage must list entries from root directory without leading slash',
        );
    }

    #[Test]
    public function listsNestedEntriesFromRootDirectory(): void
    {
        self::assertThat(
            new DiskStorage(
                (new TempFolder())
                    ->withFile('sub/nested.txt', 'data')
                    ->path(),
            ),
            new HasEntries('', ['sub/nested.txt']),
            'DiskStorage must list nested entries from root without leading slash',
        );
    }

    #[Test]
    public function listsNoEntriesForNonDirectoryLocation(): void
    {
        self::assertThat(
            new DiskStorage(
                (new TempFolder())
                    ->withFile('file.txt', 'data')
                    ->path(),
            ),
            new HasEntries('file.txt', []),
            'DiskStorage must return no entries when the location is a file, not a directory',
        );
    }

    #[Test]
    public function listsFilesFromNestedDirectories(): void
    {
        self::assertThat(
            new DiskStorage(
                (new TempFolder())
                    ->withFile('a/b/c/deep.txt', 'x')
                    ->path(),
            ),
            new HasEntries('a', [
                'a/b/c/deep.txt',
            ]),
            'DiskStorage must list files from nested subdirectories recursively',
        );
    }

    #[Test]
    public function writesFileWithCustomMode(): void
    {
        self::assertThat(
            (new DiskStorage(
                (new TempFolder())->path(),
            ))->write(new TextFile('file.txt', 'data', 0o755)),
            new HasEntry('file.txt', 'data', 0o755),
            'DiskStorage must write a file with the specified mode',
        );
    }

    #[Test]
    public function updatesFileModeWhenOverwritten(): void
    {
        self::assertSame(
            0o755,
            (new DiskStorage(
                (new TempFolder())
                    ->withFile('file.txt', 'data')
                    ->path(),
            ))->write(new TextFile('file.txt', 'data', 0o755))
                ->mode('file.txt'),
            'DiskStorage must update the file mode when overwriting an existing file',
        );
    }

    #[Test]
    public function throwsWhenReadingModeOfMissingLocation(): void
    {
        $this->expectException(SheriffException::class);

        (new DiskStorage(
            (new TempFolder())->path(),
        ))->mode('missing.txt');
    }
}
