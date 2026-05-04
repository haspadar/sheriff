<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config\Dirs;

use Override;

/**
 * Directories as negated glob patterns for exclusion (e.g. !vendor/**).
 */
final readonly class NegatedGlobDirs implements Dirs
{
    /**
     * Initializes with directory paths to negate.
     *
     * @param list<string> $dirs Directory paths to wrap as !dir/** negated globs
     */
    public function __construct(private array $dirs) {}

    #[Override]
    public function toList(): array
    {
        return array_map(static fn(string $dir): string => "!{$dir}/**", $this->dirs);
    }
}
