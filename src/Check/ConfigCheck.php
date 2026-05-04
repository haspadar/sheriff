<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Override;

/**
 * A quality check discovered from a config key with a matching command.sh.
 *
 * Example:
 *
 *     new ConfigCheck('phpstan', '/path/to/project');
 */
final readonly class ConfigCheck implements Check
{
    /**
     * Initializes with the check name and project root path.
     *
     * @param string $name Tool name matching the .sheriff subdirectory
     * @param string $root Absolute path to the project root directory
     */
    public function __construct(private string $name, private string $root) {}

    #[Override]
    public function name(): string
    {
        return $this->name;
    }

    #[Override]
    public function command(): string
    {
        return "{$this->root}/.sheriff/{$this->name}/command.sh";
    }
}
