<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config\Dirs;

use Override;

/**
 * Directories suffixed with / to match directory entries explicitly.
 */
final readonly class TrailingSlashDirs implements Dirs
{
    /**
     * Initializes with directory paths to transform.
     *
     * @param list<string> $dirs Directory paths to suffix with trailing slash
     */
    public function __construct(private array $dirs) {}

    #[Override]
    public function toList(): array
    {
        return array_map(static fn(string $dir): string => "{$dir}/", $this->dirs);
    }
}
