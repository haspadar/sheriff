<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Override;

/**
 * A single-element collection for a specifically requested check.
 */
final readonly class SingleCheck implements Checks
{
    /**
     * Initializes with the check name and project root path.
     *
     * @param string $name Tool name matching the .sheriff subdirectory
     * @param string $root Absolute path to the project root directory
     */
    public function __construct(private string $name, private string $root) {}

    #[Override]
    public function all(): iterable
    {
        yield new ConfigCheck($this->name, $this->root);
    }
}
