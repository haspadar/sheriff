<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Storage;

use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\Storage\Reaction\StorageReaction;
use Override;

/**
 * Writes a file only when its contents or mode differ from what is already stored.
 */
final readonly class DiffingStorage implements Storage
{
    /**
     * Initializes with underlying storage and a change reaction.
     *
     * @param Storage $origin Underlying storage to write through
     * @param StorageReaction $reaction Receives created, updated, and skipped notifications
     */
    public function __construct(private Storage $origin, private StorageReaction $reaction) {}

    #[Override]
    public function write(File $file): self
    {
        $path = $file->name();

        if (!$this->origin->exists($path)) {
            $newOrigin = $this->origin->write($file);
            $this->reaction->created($path);

            return new self($newOrigin, $this->reaction);
        }

        $changed = $this->origin->read($path) !== $file->contents()
            || $this->origin->mode($path) !== $file->mode();

        if (!$changed) {
            $this->reaction->skipped($path);

            return $this;
        }

        $newOrigin = $this->origin->write($file);
        $this->reaction->updated($path);

        return new self($newOrigin, $this->reaction);
    }

    #[Override]
    public function read(string $location): string
    {
        return $this->origin->read($location);
    }

    #[Override]
    public function exists(string $location): bool
    {
        return $this->origin->exists($location);
    }

    #[Override]
    public function entries(string $location): iterable
    {
        return $this->origin->entries($location);
    }

    #[Override]
    public function mode(string $location): int
    {
        return $this->origin->mode($location);
    }
}
