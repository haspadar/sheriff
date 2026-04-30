<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Storage;

use FilesystemIterator;
use Haspadar\Piqule\File\File;
use Haspadar\Piqule\PiquleException;
use Override;
use SplFileInfo;

use function assert;

/**
 * Filesystem-backed storage rooted at a given directory.
 */
final readonly class DiskStorage implements Storage
{
    private const int FULL_PERMISSIONS = 0o777;

    /**
     * Initializes storage rooted at the given directory path.
     *
     * @param string $root Absolute filesystem path used as the storage root
     */
    public function __construct(private string $root) {}

    #[Override]
    public function read(string $location): string
    {
        $path = $this->pathOf($location);

        if (!is_file($path)) {
            throw new PiquleException("Location not found: $location");
        }

        $contents = file_get_contents($path);

        if (!is_string($contents)) {
            throw new PiquleException("Unable to read location: $location");
        }

        return $contents;
    }

    #[Override]
    public function entries(string $location): iterable
    {
        $path = $this->pathOf($location);

        if (!is_dir($path)) {
            return [];
        }

        $iterator = new FilesystemIterator(
            $path,
            FilesystemIterator::SKIP_DOTS,
        );

        foreach ($iterator as $item) {
            assert($item instanceof SplFileInfo);

            if ($item->isFile()) {
                yield ltrim(
                    "{$location}/{$item->getFilename()}",
                    '/',
                );
            }

            if ($item->isDir()) {
                yield from $this->entries(
                    ltrim("{$location}/{$item->getFilename()}", '/'),
                );
            }
        }
    }

    #[Override]
    public function exists(string $location): bool
    {
        return is_file($this->pathOf($location));
    }

    #[Override]
    public function write(File $file): self
    {
        $location = $file->name();
        $path = $this->pathOf($location);
        $directory = dirname($path);

        if (!is_dir($directory)
            && !mkdir($directory, self::FULL_PERMISSIONS, true)
            && !is_dir($directory)
        ) {
            throw new PiquleException("Unable to create directory: $directory");
        }

        if (!is_int(file_put_contents($path, $file->contents()))) {
            throw new PiquleException("Unable to write location: $location");
        }

        if (!chmod($path, $file->mode())) {
            throw new PiquleException("Unable to set permissions: $location");
        }

        return $this;
    }

    #[Override]
    public function mode(string $location): int
    {
        $path = $this->pathOf($location);

        if (!is_file($path)) {
            throw new PiquleException("Location not found: $location");
        }

        $perms = fileperms($path);

        if (!is_int($perms)) {
            throw new PiquleException("Unable to read permissions: $location");
        }

        return $perms & self::FULL_PERMISSIONS;
    }

    /**
     * Resolves the absolute filesystem path for a given storage location.
     *
     * @throws PiquleException
     */
    private function pathOf(string $location): string
    {
        return (new SafePath($this->root))->resolve($location);
    }
}
