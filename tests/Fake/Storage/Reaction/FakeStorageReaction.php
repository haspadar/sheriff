<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Storage\Reaction;

use Haspadar\Sheriff\Storage\Reaction\StorageReaction;
use Override;

final class FakeStorageReaction implements StorageReaction
{
    /** @var list<string> */
    private array $created = [];

    /** @var list<string> */
    private array $updated = [];

    /** @var list<string> */
    private array $skipped = [];

    #[Override]
    public function created(string $path): void
    {
        $this->created[] = $path;
    }

    #[Override]
    public function updated(string $path): void
    {
        $this->updated[] = $path;
    }

    #[Override]
    public function skipped(string $path): void
    {
        $this->skipped[] = $path;
    }

    /** @return list<string> */
    public function createdPaths(): array
    {
        return $this->created;
    }

    /** @return list<string> */
    public function updatedPaths(): array
    {
        return $this->updated;
    }

    /** @return list<string> */
    public function skippedPaths(): array
    {
        return $this->skipped;
    }
}
