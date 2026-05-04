<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Storage;

use Haspadar\Sheriff\SheriffException;

/**
 * Resolves a relative location to an absolute path within a storage root, preventing path traversal.
 */
final readonly class SafePath
{
    /**
     * Initializes with the storage root directory.
     *
     * @param string $root Absolute filesystem path used as the storage root
     */
    public function __construct(private string $root) {}

    /**
     * Returns the safe absolute path for a relative location.
     *
     * @param string $location Relative location under the storage root
     * @throws SheriffException
     */
    public function resolve(string $location): string
    {
        $parts = [];

        foreach (explode('/', str_replace('\\', '/', $location)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                if ($parts === []) {
                    throw new SheriffException("Invalid location: $location");
                }

                array_pop($parts);

                continue;
            }

            $parts[] = $part;
        }

        return sprintf('%s/%s', rtrim($this->root, '/'), implode('/', $parts));
    }
}
