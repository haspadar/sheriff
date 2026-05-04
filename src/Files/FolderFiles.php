<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Files;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Storage\Storage;
use Override;

/**
 * Reads all files from a given storage folder as a Files collection.
 */
final readonly class FolderFiles implements Files
{
    /**
     * Initializes with a storage backend and a folder path to read from.
     *
     * @param Storage $storage Source storage to read files from
     * @param string $folder Folder path relative to storage root
     */
    public function __construct(private Storage $storage, private string $folder) {}

    #[Override]
    public function all(): iterable
    {
        foreach ($this->storage->entries($this->folder) as $path) {
            yield new TextFile(
                $path,
                $this->storage->read($path),
                $this->storage->mode($path),
            );
        }
    }
}
