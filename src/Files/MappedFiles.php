<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Files;

use Closure;
use Haspadar\Sheriff\File\File;
use Override;

/**
 * Applies a transformation closure to each file in the wrapped collection.
 */
final readonly class MappedFiles implements Files
{
    /**
     * Initializes with a file collection and a transformation closure.
     *
     * @param Files $origin Underlying file collection to transform
     * @param Closure(File): File $map Transformation applied to each file in $origin
     */
    public function __construct(private Files $origin, private Closure $map) {}

    #[Override]
    public function all(): iterable
    {
        foreach ($this->origin->all() as $file) {
            yield ($this->map)($file);
        }
    }
}
