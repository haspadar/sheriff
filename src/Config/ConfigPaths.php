<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

/**
 * Paths used to locate configuration files.
 */
final readonly class ConfigPaths
{
    /**
     * Initializes with an optional custom path for the sheriff defaults YAML file.
     *
     * @param string $config Path to the sheriff defaults YAML file
     */
    public function __construct(
        private string $config = __DIR__ . '/../../templates/always/.sheriff/config.yaml',
    ) {}

    /** Returns the config.yaml file path. */
    public function configYaml(): string
    {
        return $this->config;
    }
}
