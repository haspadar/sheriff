<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Storage;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Storage\DiffingStorage;
use Haspadar\Sheriff\Storage\InMemoryStorage;
use Haspadar\Sheriff\Tests\Fake\Storage\Reaction\FakeStorageReaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DiffingStorageTest extends TestCase
{
    #[Test]
    public function reportsCreatedWhenFileDoesNotExist(): void
    {
        $reaction = new FakeStorageReaction();

        (new DiffingStorage(
            new InMemoryStorage(),
            $reaction,
        ))->write(
            new TextFile('created.txt', 'data'),
        );

        self::assertSame(
            ['created.txt'],
            $reaction->createdPaths(),
            'DiffingStorage must report created() when writing a file that does not exist',
        );
    }

    #[Test]
    public function reportsUpdatedWhenFileExistsWithDifferentContents(): void
    {
        $reaction = new FakeStorageReaction();

        (new DiffingStorage(
            new InMemoryStorage([
                'updated.txt' => new TextFile('updated.txt', 'old'),
            ]),
            $reaction,
        ))->write(
            new TextFile('updated.txt', 'new'),
        );

        self::assertSame(
            ['updated.txt'],
            $reaction->updatedPaths(),
            'DiffingStorage must report updated() when contents differ',
        );
    }

    #[Test]
    public function reportsUpdatedWhenModeChanges(): void
    {
        $reaction = new FakeStorageReaction();

        (new DiffingStorage(
            new InMemoryStorage([
                'file.txt' => new TextFile('file.txt', 'data', 0o644),
            ]),
            $reaction,
        ))->write(
            new TextFile('file.txt', 'data', 0o755),
        );

        self::assertSame(
            ['file.txt'],
            $reaction->updatedPaths(),
            'DiffingStorage must report updated() when the file mode changes',
        );
    }

    #[Test]
    public function doesNotReportCreatedWhenContentsAndModeAreTheSame(): void
    {
        $reaction = new FakeStorageReaction();

        (new DiffingStorage(
            new InMemoryStorage([
                'same.txt' => new TextFile('same.txt', 'data', 0o644),
            ]),
            $reaction,
        ))->write(
            new TextFile('same.txt', 'data', 0o644),
        );

        self::assertSame(
            [],
            $reaction->createdPaths(),
            'DiffingStorage must not report created() when contents and mode are identical',
        );
    }

    #[Test]
    public function reportsSkippedWhenContentsAndModeAreTheSame(): void
    {
        $reaction = new FakeStorageReaction();

        (new DiffingStorage(
            new InMemoryStorage([
                'same.txt' => new TextFile('same.txt', 'data', 0o644),
            ]),
            $reaction,
        ))->write(
            new TextFile('same.txt', 'data', 0o644),
        );

        self::assertSame(
            ['same.txt'],
            $reaction->skippedPaths(),
            'DiffingStorage must report skipped() when contents and mode are identical',
        );
    }

    #[Test]
    public function doesNotReportUpdatedWhenContentsAndModeAreTheSame(): void
    {
        $reaction = new FakeStorageReaction();

        (new DiffingStorage(
            new InMemoryStorage([
                'same.txt' => new TextFile('same.txt', 'data', 0o644),
            ]),
            $reaction,
        ))->write(
            new TextFile('same.txt', 'data', 0o644),
        );

        self::assertSame(
            [],
            $reaction->updatedPaths(),
            'DiffingStorage must not report updated() when contents and mode are identical',
        );
    }

    #[Test]
    public function delegatesReadToOrigin(): void
    {
        self::assertSame(
            'data',
            (new DiffingStorage(
                new InMemoryStorage([
                    'a.txt' => new TextFile('a.txt', 'data'),
                ]),
                new FakeStorageReaction(),
            ))->read('a.txt'),
            'DiffingStorage must delegate read() to the origin storage',
        );
    }

    #[Test]
    public function delegatesExistsToOrigin(): void
    {
        self::assertTrue(
            (new DiffingStorage(
                new InMemoryStorage([
                    'b.txt' => new TextFile('b.txt', 'x'),
                ]),
                new FakeStorageReaction(),
            ))->exists('b.txt'),
            'DiffingStorage must delegate exists() to the origin storage',
        );
    }

    #[Test]
    public function delegatesEntriesToOrigin(): void
    {
        self::assertSame(
            ['a/one.txt', 'a/two.txt'],
            iterator_to_array(
                (new DiffingStorage(
                    new InMemoryStorage([
                        'a/one.txt' => new TextFile('a/one.txt', '1'),
                        'a/two.txt' => new TextFile('a/two.txt', '2'),
                    ]),
                    new FakeStorageReaction(),
                ))->entries('a'),
            ),
            'DiffingStorage must delegate entries() to the origin storage',
        );
    }

    #[Test]
    public function delegatesModeToOrigin(): void
    {
        self::assertSame(
            0o755,
            (new DiffingStorage(
                new InMemoryStorage([
                    'x.sh' => new TextFile('x.sh', 'echo x', 0o755),
                ]),
                new FakeStorageReaction(),
            ))->mode('x.sh'),
            'DiffingStorage must delegate mode() to the origin storage',
        );
    }
}
