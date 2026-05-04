<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Storage\Reaction;

use Haspadar\Sheriff\Storage\Reaction\StorageReactions;
use Haspadar\Sheriff\Tests\Fake\Storage\Reaction\FakeStorageReaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StorageReactionsTest extends TestCase
{
    #[Test]
    public function delegatesCreatedToAllReactions(): void
    {
        $first = new FakeStorageReaction();
        $second = new FakeStorageReaction();

        (new StorageReactions([$first, $second]))
            ->created('file.txt');

        self::assertSame(
            ['file.txt'],
            $first->createdPaths(),
            'StorageReactions must delegate created() to all contained reactions',
        );
    }

    #[Test]
    public function delegatesUpdatedToAllReactions(): void
    {
        $first = new FakeStorageReaction();
        $second = new FakeStorageReaction();

        (new StorageReactions([$first, $second]))
            ->updated('file.txt');

        self::assertSame(
            ['file.txt'],
            $second->updatedPaths(),
            'StorageReactions must delegate updated() to all contained reactions',
        );
    }

    #[Test]
    public function delegatesSkippedToFirstReaction(): void
    {
        $first = new FakeStorageReaction();
        $second = new FakeStorageReaction();

        (new StorageReactions([$first, $second]))
            ->skipped('file.txt');

        self::assertSame(
            ['file.txt'],
            $first->skippedPaths(),
            'StorageReactions must delegate skipped() to the first reaction',
        );
    }

    #[Test]
    public function delegatesSkippedToSecondReaction(): void
    {
        $first = new FakeStorageReaction();
        $second = new FakeStorageReaction();

        (new StorageReactions([$first, $second]))
            ->skipped('file.txt');

        self::assertSame(
            ['file.txt'],
            $second->skippedPaths(),
            'StorageReactions must delegate skipped() to the second reaction',
        );
    }
}
