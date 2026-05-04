<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\File;

use Override;

/**
 * Prepends a path prefix to the wrapped file's name.
 */
final readonly class PrefixedFile implements File
{
    /**
     * Initializes with a path prefix and the file to decorate.
     *
     * @param string $prefix Path segment to prepend to the file name
     * @param File $origin File whose name will be prefixed
     */
    public function __construct(private string $prefix, private File $origin) {}

    #[Override]
    public function name(): string
    {
        $pathPrefix = rtrim($this->prefix, '/');
        $name = ltrim($this->origin->name(), '/');

        return $pathPrefix === ''
            ? $name
            : "{$pathPrefix}/{$name}";
    }

    #[Override]
    public function contents(): string
    {
        return $this->origin->contents();
    }

    #[Override]
    public function mode(): int
    {
        return $this->origin->mode();
    }
}
