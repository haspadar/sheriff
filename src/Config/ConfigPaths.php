<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

/**
 * Paths used to locate configuration files.
 */
final readonly class ConfigPaths
{
    /**
     * Initializes with optional custom paths for composer.json and config.yaml.
     *
     * @param string $composer Path to the project composer.json (empty means none)
     * @param string $config Path to the sheriff defaults YAML file
     */
    public function __construct(
        private string $composer = '',
        private string $config = __DIR__ . '/../../templates/always/.sheriff/config.yaml',
    ) {}

    /** Returns the composer.json file path. */
    public function composerJson(): string
    {
        return $this->composer;
    }

    /** Returns the config.yaml file path. */
    public function configYaml(): string
    {
        return $this->config;
    }
}
