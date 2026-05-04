<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Storage;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Storage\InMemoryStorage;
use Haspadar\Sheriff\Storage\OnceStorage;
use Haspadar\Sheriff\Tests\Constraint\Storage\ReactionWasSilent;
use Haspadar\Sheriff\Tests\Fake\Storage\Reaction\FakeStorageReaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OnceStorageTest extends TestCase
{
    #[Test]
    public function createsFileWhenItDoesNotExist(): void
    {
        $reaction = new FakeStorageReaction();

        (new OnceStorage(
            new InMemoryStorage(),
            $reaction,
        ))->write(new TextFile('init.env', 'APP_ENV=local'));

        self::assertSame(
            ['init.env'],
            $reaction->createdPaths(),
            'created() must be called for a new file',
        );
    }

    #[Test]
    public function skipsFileWhenItAlreadyExists(): void
    {
        $reaction = new FakeStorageReaction();

        (new OnceStorage(
            new InMemoryStorage([
                'config.php' => new TextFile('config.php', '<?php // original'),
            ]),
            $reaction,
        ))->write(new TextFile('config.php', '<?php // new'));

        self::assertThat(
            $reaction,
            new ReactionWasSilent(),
            'no reaction must be triggered when file already exists',
        );
    }

    #[Test]
    public function returnsSameInstanceWhenFileAlreadyExists(): void
    {
        $storage = new OnceStorage(
            new InMemoryStorage([
                'config.php' => new TextFile('config.php', '<?php // original'),
            ]),
            new FakeStorageReaction(),
        );

        self::assertSame(
            $storage,
            $storage->write(new TextFile('config.php', '<?php // new')),
            'write() must return the same instance when file already exists',
        );
    }

    #[Test]
    public function returnsNewInstanceAfterCreation(): void
    {
        $storage = new OnceStorage(
            new InMemoryStorage(),
            new FakeStorageReaction(),
        );

        $result = $storage->write(new TextFile('run.sh', '#!/bin/bash'));

        self::assertNotSame(
            $storage,
            $result,
            'write() must return a new instance to preserve immutability',
        );
    }

    #[Test]
    public function readsFileContentFromOrigin(): void
    {
        self::assertSame(
            '<?php',
            (new OnceStorage(
                new InMemoryStorage(['bootstrap.php' => new TextFile('bootstrap.php', '<?php')]),
                new FakeStorageReaction(),
            ))->read('bootstrap.php'),
            'read() must delegate to origin storage',
        );
    }

    #[Test]
    public function listsEntriesFromOrigin(): void
    {
        self::assertContains(
            'env.php',
            (new OnceStorage(
                new InMemoryStorage(['env.php' => new TextFile('env.php', '')]),
                new FakeStorageReaction(),
            ))->entries(''),
            'entries() must delegate to origin storage',
        );
    }

    #[Test]
    public function returnsModeFromOrigin(): void
    {
        self::assertSame(
            0o755,
            (new OnceStorage(
                new InMemoryStorage(['deploy.sh' => new TextFile('deploy.sh', '', 0o755)]),
                new FakeStorageReaction(),
            ))->mode('deploy.sh'),
            'mode() must delegate to origin storage',
        );
    }

    #[Test]
    public function checksExistenceViaOrigin(): void
    {
        self::assertTrue(
            (new OnceStorage(
                new InMemoryStorage(['app.php' => new TextFile('app.php', '<?php')]),
                new FakeStorageReaction(),
            ))->exists('app.php'),
            'exists() must delegate to origin storage',
        );
    }

    #[Test]
    public function checksNonExistenceViaOrigin(): void
    {
        self::assertFalse(
            (new OnceStorage(
                new InMemoryStorage(),
                new FakeStorageReaction(),
            ))->exists('missing.php'),
            'exists() must return false when file is absent in origin',
        );
    }
}
