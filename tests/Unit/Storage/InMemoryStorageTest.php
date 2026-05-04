<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Storage;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Storage\InMemoryStorage;
use Haspadar\Sheriff\Tests\Constraint\Storage\HasEntries;
use Haspadar\Sheriff\Tests\Constraint\Storage\HasEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InMemoryStorageTest extends TestCase
{
    #[Test]
    public function readsWrittenContents(): void
    {
        self::assertThat(
            (new InMemoryStorage())->write(
                new TextFile('read/file.txt', 'hello'),
            ),
            new HasEntry('read/file.txt', 'hello'),
            'InMemoryStorage must make written contents readable',
        );
    }

    #[Test]
    public function overwritesExistingContentsWhenWritingToSamePath(): void
    {
        $path = './overwrite.txt';

        self::assertThat(
            (new InMemoryStorage())
                ->write(new TextFile($path, 'first'))
                ->write(new TextFile($path, 'second')),
            new HasEntry($path, 'second'),
            'InMemoryStorage must overwrite existing contents when writing to the same path',
        );
    }

    #[Test]
    public function reportsNonExistingLocation(): void
    {
        self::assertFalse(
            (new InMemoryStorage())->exists('missing/location.txt'),
            'Storage must report non-existing location',
        );
    }

    #[Test]
    public function reportsExistingLocation(): void
    {
        self::assertTrue(
            (new InMemoryStorage())
                ->write(new TextFile('exists/data.bin', 'data'))
                ->exists('exists/data.bin'),
            'Storage must report existing location',
        );
    }

    #[Test]
    public function doesNotMutateOriginalStorage(): void
    {
        $storage = new InMemoryStorage();

        $storage->write(new TextFile('../immutable.txt', 'data'));

        self::assertFalse(
            $storage->exists('../immutable.txt'),
            'Initial storage must not be modified after write',
        );
    }

    #[Test]
    public function returnsNewStorageWithWrittenEntry(): void
    {
        self::assertTrue(
            (new InMemoryStorage())
                ->write(new TextFile('new/file.log', 'data'))
                ->exists('new/file.log'),
            'Write must return a new storage instance with updated state',
        );
    }

    #[Test]
    public function throwsWhenReadingMissingLocation(): void
    {
        $this->expectException(SheriffException::class);

        (new InMemoryStorage())->read('./absent.txt');
    }

    #[Test]
    public function listsEntriesUnderGivenLocation(): void
    {
        self::assertThat(
            (new InMemoryStorage())
                ->write(new TextFile('alpha/file-1.log', '1'))
                ->write(new TextFile('alpha/file-2.log', '2'))
                ->write(new TextFile('beta/ignored.log', 'x')),
            new HasEntries('alpha', [
                'alpha/file-1.log',
                'alpha/file-2.log',
            ]),
            'InMemoryStorage must list only entries under the given location',
        );
    }

    #[Test]
    public function doesNotListNestedEntries(): void
    {
        self::assertThat(
            (new InMemoryStorage())
                ->write(new TextFile('root/level1/deep.txt', 'x'))
                ->write(new TextFile('root/shallow.txt', '1')),
            new HasEntries('root', [
                'root/shallow.txt',
            ]),
            'InMemoryStorage must not list deeply nested entries when listing a location',
        );
    }

    #[Test]
    public function listsNoEntriesWhenLocationHasOnlyNestedFiles(): void
    {
        self::assertThat(
            (new InMemoryStorage())
                ->write(new TextFile('container/nested/one.dat', '1'))
                ->write(new TextFile('container/nested/two.dat', '2')),
            new HasEntries('container', []),
            'InMemoryStorage must return no entries when all files are in nested subdirectories',
        );
    }

    #[Test]
    public function storesAndReturnsMode(): void
    {
        self::assertSame(
            0o755,
            (new InMemoryStorage())
                ->write(new TextFile('file.txt', 'data', 0o755))
                ->mode('file.txt'),
            'InMemoryStorage must store and return the file mode',
        );
    }

    #[Test]
    public function throwsWhenReadingModeForMissingLocation(): void
    {
        $this->expectException(SheriffException::class);

        (new InMemoryStorage())->mode('missing.txt');
    }

    #[Test]
    public function listsEntriesWhenLocationHasTrailingSlash(): void
    {
        self::assertThat(
            (new InMemoryStorage())
                ->write(new TextFile('dir/a.txt', '1'))
                ->write(new TextFile('dir/b.txt', '2')),
            new HasEntries('dir/', [
                'dir/a.txt',
                'dir/b.txt',
            ]),
            'InMemoryStorage must handle trailing slash in location',
        );
    }

    #[Test]
    public function collectsAllMatchingEntriesNotJustFirst(): void
    {
        $storage = (new InMemoryStorage())
            ->write(new TextFile('other/skip.txt', 'x'))
            ->write(new TextFile('dir/first.txt', '1'))
            ->write(new TextFile('another/skip.txt', 'x'))
            ->write(new TextFile('dir/second.txt', '2'));

        self::assertThat(
            $storage,
            new HasEntries('dir', [
                'dir/first.txt',
                'dir/second.txt',
            ]),
            'InMemoryStorage must collect all matching entries even when non-matching entries are interleaved',
        );
    }
}
