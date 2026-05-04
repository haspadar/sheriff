<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Storage;

use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\Storage\Reaction\StorageReaction;
use Override;

/**
 * Writes a file only if it does not already exist in the underlying storage.
 */
final readonly class OnceStorage implements Storage
{
    /**
     * Initializes with underlying storage and a creation reaction.
     *
     * @param Storage $origin Underlying storage to write through
     * @param StorageReaction $reaction Receives a created notification on the first write
     */
    public function __construct(private Storage $origin, private StorageReaction $reaction) {}

    #[Override]
    public function write(File $file): self
    {
        if ($this->origin->exists($file->name())) {
            return $this;
        }

        $newOrigin = $this->origin->write($file);
        $this->reaction->created($file->name());

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
