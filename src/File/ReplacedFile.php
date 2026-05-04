<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\File;

use Override;

/**
 * Performs a literal string replacement in the wrapped file's contents.
 */
final readonly class ReplacedFile implements File
{
    /**
     * Initializes with the original file and replacement pair.
     *
     * @param File $origin File whose contents are subject to replacement
     * @param string $search Literal substring to search for
     * @param string $replace Literal substring to substitute for each match
     */
    public function __construct(
        private File $origin,
        private string $search,
        private string $replace,
    ) {}

    #[Override]
    public function name(): string
    {
        return $this->origin->name();
    }

    #[Override]
    public function contents(): string
    {
        return str_replace(
            $this->search,
            $this->replace,
            $this->origin->contents(),
        );
    }

    #[Override]
    public function mode(): int
    {
        return $this->origin->mode();
    }
}
