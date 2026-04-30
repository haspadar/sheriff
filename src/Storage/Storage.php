<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Storage;

use Haspadar\Piqule\File\File;
use Haspadar\Piqule\PiquleException;
use UnexpectedValueException;

/**
 * Persistent key-value store for named files
 */
interface Storage
{
    /**
     * Reads contents from the given location
     *
     * @throws PiquleException if the location does not exist
     */
    public function read(string $location): string;

    /**
     * Persists the given file into this storage
     *
     * @throws PiquleException
     */
    public function write(File $file): self;

    /**
     * Checks whether a projection exists at the given location
     *
     * @throws PiquleException
     */
    public function exists(string $location): bool;

    /**
     * Lists entries under the given location
     *
     * @throws PiquleException|UnexpectedValueException
     * @return iterable<string> relative entry paths
     */
    public function entries(string $location): iterable;

    /**
     * Retrieves the file mode (permissions) at the given location
     *
     * @throws PiquleException if the location does not exist
     */
    public function mode(string $location): int;
}
