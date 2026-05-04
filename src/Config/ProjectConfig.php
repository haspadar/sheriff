<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Loads project configuration from .sheriff.yaml, .sheriff.php, or defaults.
 *
 * Example:
 *
 *     new ProjectConfig('/path/to/project');
 */
final readonly class ProjectConfig implements Config
{
    private StickyConfig $config;

    /**
     * Initializes with the project root directory path.
     *
     * @param string $root Absolute path to the project root directory
     */
    public function __construct(private string $root)
    {
        $this->config = new StickyConfig($this->resolve(...));
    }

    #[Override]
    public function has(string $name): bool
    {
        return $this->config->has($name);
    }

    #[Override]
    public function list(string $name): array
    {
        return $this->config->list($name);
    }

    #[Override]
    public function toArray(): array
    {
        return $this->config->toArray();
    }

    /**
     * Resolves configuration from .sheriff.yaml, .sheriff.php, or defaults.
     *
     * @throws SheriffException
     */
    private function resolve(): Config
    {
        $defaults = new DefaultConfig(
            [],
            [],
            new ConfigPaths(sprintf('%s/composer.json', $this->root)),
        );

        $yamlPath = sprintf('%s/.sheriff.yaml', $this->root);
        $phpPath = sprintf('%s/.sheriff.php', $this->root);

        if (file_exists($yamlPath)) {
            return new YamlConfig($yamlPath, $defaults);
        }

        if (file_exists($phpPath)) {
            $loaded = require $phpPath;

            if (!$loaded instanceof Config) {
                throw new SheriffException('.sheriff.php must return an instance of Config');
            }

            return $loaded;
        }

        return $defaults;
    }
}
