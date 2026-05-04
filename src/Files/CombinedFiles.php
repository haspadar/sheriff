<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Files;

use Override;

/**
 * Merges multiple Files sources into a single sequential collection.
 */
final readonly class CombinedFiles implements Files
{
    /**
     * Initializes with multiple file sources to merge.
     *
     * @param list<Files> $sources File collections concatenated in the order given
     */
    public function __construct(private array $sources) {}

    #[Override]
    public function all(): iterable
    {
        foreach ($this->sources as $files) {
            yield from $files->all();
        }
    }
}
