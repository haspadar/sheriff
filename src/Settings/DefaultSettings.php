<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings;

use Haspadar\Sheriff\Settings\Value\RawValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;
use stdClass;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Settings backed by the built-in defaults declared in Sheriff's config.yaml.
 *
 * Example:
 *
 *     new DefaultSettings();
 *
 *     new DefaultSettings('/custom/path/config.yaml');
 */
final readonly class DefaultSettings implements Settings
{
    private stdClass $cache;

    /**
     * Initializes with the path to the defaults YAML file.
     *
     * @param string $path Filesystem path to the config.yaml that declares defaults
     */
    public function __construct(
        private string $path = __DIR__ . '/../../templates/always/.sheriff/config.yaml',
    ) {
        $this->cache = new stdClass();
    }

    #[Override]
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->defaults());
    }

    #[Override]
    public function value(string $name): Value
    {
        if (!$this->has($name)) {
            throw new SheriffException(sprintf('Unknown config key "%s"', $name));
        }

        return (new RawValue($this->defaults()[$name]))->value();
    }

    #[Override]
    public function keys(): array
    {
        return array_keys($this->defaults());
    }

    /**
     * Parses the YAML file and caches the defaults map.
     *
     * @throws SheriffException
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        if (isset($this->cache->value)) {
            /** @var array<string, mixed> $cached */
            $cached = $this->cache->value;

            return $cached;
        }

        try {
            $yaml = Yaml::parseFile($this->path);
        } catch (ParseException $e) {
            throw new SheriffException(
                sprintf('Failed to parse config "%s": %s', $this->path, $e->getMessage()),
                0,
                $e,
            );
        }

        if (!is_array($yaml) || !array_key_exists('defaults', $yaml) || !is_array($yaml['defaults'])) {
            throw new SheriffException('Missing "defaults" section in config.yaml');
        }

        /** @var array<string, mixed> $defaults */
        $defaults = $yaml['defaults'];
        $this->cache->value = $defaults;

        return $defaults;
    }
}
