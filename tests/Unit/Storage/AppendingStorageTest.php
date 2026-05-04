<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Storage;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Storage\AppendingStorage;
use Haspadar\Sheriff\Storage\InMemoryStorage;
use Haspadar\Sheriff\Tests\Constraint\Storage\HasEntries;
use Haspadar\Sheriff\Tests\Constraint\Storage\HasEntry;
use Haspadar\Sheriff\Tests\Constraint\Storage\ReactionWasSilent;
use Haspadar\Sheriff\Tests\Fake\Storage\Reaction\FakeStorageReaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AppendingStorageTest extends TestCase
{
    #[Test]
    public function createsFileWhenItDoesNotExist(): void
    {
        $reaction = new FakeStorageReaction();

        (new AppendingStorage(
            new InMemoryStorage(),
            $reaction,
            '# deploy-check',
        ))->write(new TextFile('draft.txt', '# deploy-check'));

        self::assertSame(
            ['draft.txt'],
            $reaction->createdPaths(),
            'created() must be called when file does not exist',
        );
    }

    #[Test]
    public function reportsUpdatedWhenMarkerIsAbsent(): void
    {
        $reaction = new FakeStorageReaction();

        (new AppendingStorage(
            new InMemoryStorage([
                'config.php' => new TextFile('config.php', "<?php\necho 'existing';"),
            ]),
            $reaction,
            '# vendor-marker',
        ))->write(new TextFile('config.php', '# vendor-marker'));

        self::assertSame(
            ['config.php'],
            $reaction->updatedPaths(),
            'updated() must be called when marker is absent',
        );
    }

    #[Test]
    public function appendsBlockAfterExistingContentWhenMarkerIsAbsent(): void
    {
        $result = (new AppendingStorage(
            new InMemoryStorage([
                'notes.md' => new TextFile('notes.md', "## Appendable\nbody"),
            ]),
            new FakeStorageReaction(),
            '# merge-block',
        ))->write(new TextFile('notes.md', '# merge-block'));

        self::assertThat(
            $result,
            new HasEntry('notes.md', "## Appendable\nbody\n# merge-block"),
            'storage must contain original content followed by appended block',
        );
    }

    #[Test]
    public function skipsWriteWhenMarkerAlreadyPresent(): void
    {
        $reaction = new FakeStorageReaction();

        (new AppendingStorage(
            new InMemoryStorage([
                'rules.neon' => new TextFile('rules.neon', "includes:\n  - base.neon\n# patch-marker"),
            ]),
            $reaction,
            '# patch-marker',
        ))->write(new TextFile('rules.neon', '# patch-marker'));

        self::assertThat(
            $reaction,
            new ReactionWasSilent(),
            'no reaction must be triggered when marker is already present',
        );
    }

    #[Test]
    public function returnsSameInstanceWhenMarkerAlreadyPresent(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage([
                'state.xml' => new TextFile('state.xml', "<config />\n# commit-marker"),
            ]),
            new FakeStorageReaction(),
            '# commit-marker',
        );

        self::assertSame(
            $storage,
            $storage->write(new TextFile('state.xml', '# commit-marker')),
            'write() must return the same instance when marker is already present',
        );
    }

    #[Test]
    public function returnsNewInstanceAfterCreation(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage(),
            new FakeStorageReaction(),
            '# create-marker',
        );

        $result = $storage->write(new TextFile('created.yaml', '# create-marker'));

        self::assertNotSame(
            $storage,
            $result,
            'write() must return a new instance to preserve immutability',
        );
    }

    #[Test]
    public function returnsNewInstanceAfterAppend(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage([
                'settings.ini' => new TextFile('settings.ini', "[entry]\nname=expandable"),
            ]),
            new FakeStorageReaction(),
            '# append-marker',
        );

        $result = $storage->write(new TextFile('settings.ini', '# append-marker'));

        self::assertNotSame(
            $storage,
            $result,
            'write() must return a new instance after appending content',
        );
    }

    #[Test]
    public function confirmsPresentFileExistsInOrigin(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage(['present.txt' => new TextFile('present.txt', 'data')]),
            new FakeStorageReaction(),
            '# exists-marker',
        );

        self::assertTrue(
            $storage->exists('present.txt'),
            'exists() must return true when file exists in origin',
        );
    }

    #[Test]
    public function confirmsAbsentFileDoesNotExistInOrigin(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage(),
            new FakeStorageReaction(),
            '# exists-marker',
        );

        self::assertFalse(
            $storage->exists('missing.txt'),
            'exists() must return false when file is absent in origin',
        );
    }

    #[Test]
    public function readsFileContentFromOrigin(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage(['record.json' => new TextFile('record.json', '{"name":"readable"}')]),
            new FakeStorageReaction(),
            '# read-marker',
        );

        self::assertThat(
            $storage,
            new HasEntry('record.json', '{"name":"readable"}'),
            'read() must delegate to origin storage',
        );
    }

    #[Test]
    public function listsEntriesFromOrigin(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage(['list.txt' => new TextFile('list.txt', 'listed')]),
            new FakeStorageReaction(),
            '# list-marker',
        );

        self::assertThat(
            $storage,
            new HasEntries('', ['list.txt']),
            'entries() must delegate to origin storage',
        );
    }

    #[Test]
    public function preservesOriginalModeWhenAppending(): void
    {
        $result = (new AppendingStorage(
            new InMemoryStorage(['hook' => new TextFile('hook', "#!/bin/sh\nexit 0", 0o755)]),
            new FakeStorageReaction(),
            '# sheriff',
        ))->write(new TextFile('hook', '# sheriff', 0o644));

        self::assertThat(
            $result,
            new HasEntry('hook', "#!/bin/sh\nexit 0\n# sheriff", 0o755),
            'appended file must retain the original file mode, not the incoming template mode',
        );
    }

    #[Test]
    public function returnsModeFromOrigin(): void
    {
        $storage = new AppendingStorage(
            new InMemoryStorage(['script.bin' => new TextFile('script.bin', '', 0o755)]),
            new FakeStorageReaction(),
            '# mode-marker',
        );

        self::assertSame(
            0o755,
            $storage->mode('script.bin'),
            'mode() must delegate to origin storage',
        );
    }
}
