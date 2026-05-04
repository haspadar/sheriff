<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Config;

use Haspadar\Piqule\PiquleException;
use Override;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads project configuration from a .piqule.yaml file.
 *
 * Supports two sections:
 * - override: replaces default values
 * - append: adds values to existing lists
 *
 * Example .piqule.yaml:
 *
 *     override:
 *         phpstan.level: 8
 *     append:
 *         phpstan.neon_includes:
 *             - ../../rules.neon
 *         infra.exclude:
 *             - dist
 */
final readonly class YamlConfig implements Config
{
    private StickyConfig $config;

    /**
     * Initializes with a YAML file path and default configuration.
     *
     * @param string $path Path to the .piqule.yaml project configuration file
     * @param DefaultConfig $defaults Base configuration providing built-in key defaults
     */
    public function __construct(private string $path, private DefaultConfig $defaults)
    {
        $this->config = new StickyConfig($this->parse(...));
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
     * Parses the YAML file and builds the layered configuration.
     *
     * @throws PiquleException
     */
    private function parse(): Config
    {
        try {
            $data = Yaml::parseFile($this->path);
        } catch (YamlParseException $e) {
            throw new PiquleException(
                sprintf('Failed to parse "%s": %s', $this->path, $e->getMessage()),
                0,
                $e,
            );
        }

        if (!is_array($data)) {
            throw new PiquleException(
                sprintf('Invalid configuration file "%s": expected a mapping', $this->path),
            );
        }

        /** @var array<string, mixed> $overrides */
        $overrides = array_key_exists('override', $data) && is_array($data['override'])
            ? $data['override']
            : [];
        $overrides = (new SheriffOverrides($overrides))->toArray();

        /** @var array<string, mixed> $appends */
        $appends = array_key_exists('append', $data) && is_array($data['append'])
            ? $data['append']
            : [];

        $pathKeys = new YamlPathKeys($overrides, $appends, $this->defaults);
        $remaining = ['infra.exclude', 'php.src'];

        return new AppendConfig(
            new OverrideConfig(
                new DefaultConfig(
                    $pathKeys->phpSrc(),
                    $pathKeys->infraExclude(),
                    $this->defaults->configPaths(),
                ),
                array_diff_key($overrides, array_flip($remaining)),
            ),
            array_diff_key($appends, array_flip($remaining)),
        );
    }
}
