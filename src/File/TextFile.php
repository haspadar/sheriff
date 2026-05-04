<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\File;

use Override;

/**
 * An in-memory file with an explicit name, contents, and mode.
 */
final readonly class TextFile implements File
{
    /**
     * Initializes with a file name, contents, and optional permission mode.
     *
     * @param string $name Relative file path used as the file's identity
     * @param string $contents Raw text content of the file
     * @param int $mode POSIX permission bits to apply when the file is written
     */
    public function __construct(
        private string $name,
        private string $contents,
        private int $mode = 0o644,
    ) {}

    #[Override]
    public function name(): string
    {
        return $this->name;
    }

    #[Override]
    public function contents(): string
    {
        return $this->contents;
    }

    #[Override]
    public function mode(): int
    {
        return $this->mode;
    }
}
