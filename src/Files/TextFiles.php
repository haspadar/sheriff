<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Files;

use Haspadar\Sheriff\File\TextFile;
use Override;

/**
 * A Files collection built from a map of path => content strings.
 */
final readonly class TextFiles implements Files
{
    /**
     * Initializes with a map of file paths to their contents.
     *
     * @param array<string, string> $files Relative file paths mapped to their text contents
     */
    public function __construct(private array $files) {}

    #[Override]
    public function all(): iterable
    {
        foreach ($this->files as $path => $contents) {
            yield new TextFile($path, $contents);
        }
    }
}
