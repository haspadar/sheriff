<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Files;

use Closure;
use Haspadar\Sheriff\File\File;
use Override;

/**
 * Yields only the files from the wrapped collection that satisfy a predicate.
 */
final readonly class FilteredFiles implements Files
{
    /**
     * Initializes with a file collection and a filtering predicate.
     *
     * @param Files $origin Underlying file collection to filter
     * @param Closure(File): bool $predicate Callback returning true for files to keep
     */
    public function __construct(private Files $origin, private Closure $predicate) {}

    #[Override]
    public function all(): iterable
    {
        foreach ($this->origin->all() as $file) {
            if (($this->predicate)($file)) {
                yield $file;
            }
        }
    }
}
