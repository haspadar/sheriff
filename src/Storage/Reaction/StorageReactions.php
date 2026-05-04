<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Storage\Reaction;

use Override;

/**
 * Composite StorageReaction that broadcasts events to a list of reactions.
 */
final readonly class StorageReactions implements StorageReaction
{
    /**
     * Initializes with a list of reactions to broadcast to.
     *
     * @param list<StorageReaction> $reactions Reactions that receive each event in order
     */
    public function __construct(private array $reactions) {}

    #[Override]
    public function created(string $path): void
    {
        foreach ($this->reactions as $reaction) {
            $reaction->created($path);
        }
    }

    #[Override]
    public function updated(string $path): void
    {
        foreach ($this->reactions as $reaction) {
            $reaction->updated($path);
        }
    }

    #[Override]
    public function skipped(string $path): void
    {
        foreach ($this->reactions as $reaction) {
            $reaction->skipped($path);
        }
    }
}
