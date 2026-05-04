<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Storage\Reaction;

use Haspadar\Sheriff\Output\Output;
use Override;

/**
 * Reports created and updated storage events to an Output channel.
 */
final readonly class ReportingStorageReaction implements StorageReaction
{
    /**
     * Initializes with the output channel for reporting.
     *
     * @param Output $output Channel to write storage event messages to
     */
    public function __construct(private Output $output) {}

    #[Override]
    public function created(string $path): void
    {
        $this->output->success(
            sprintf('Created: %s', $path),
        );
    }

    #[Override]
    public function updated(string $path): void
    {
        $this->output->info(
            sprintf('Updated: %s', $path),
        );
    }

    #[Override]
    public function skipped(string $path): void
    {
        $this->output->muted(
            sprintf('Skipped: %s', $path),
        );
    }
}
