<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

use Haspadar\Sheriff\Config\Dirs\ProjectDirs;
use Haspadar\Sheriff\Config\Dirs\TrailingGlobDirs;
use Haspadar\Sheriff\Config\Dirs\TrailingSlashDirs;
use Haspadar\Sheriff\SheriffException;
use Override;
use stdClass;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Built-in configuration with all declared keys and their default values.
 *
 * Example:
 *
 *     new DefaultConfig();
 *
 *     new DefaultConfig(paths: new ConfigPaths(composer: '/path/to/composer.json'));
 */
final readonly class DefaultConfig implements Config
{
    private stdClass $cache;

    /**
     * Initializes with source directories, infra exclusions, and config paths.
     *
     * @param list<string> $source PHP source directories (empty falls back to YAML defaults)
     * @param list<string> $infra Directories skipped by infrastructure linters (empty falls back to YAML defaults)
     * @param ConfigPaths $paths File locations for composer.json and defaults YAML
     */
    public function __construct(
        private array $source = [],
        private array $infra = [],
        private ConfigPaths $paths = new ConfigPaths(),
    ) {
        $this->cache = new stdClass();
    }

    #[Override]
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->defaults());
    }

    #[Override]
    public function list(string $name): array
    {
        if (!$this->has($name)) {
            return [];
        }

        $value = $this->defaults()[$name];

        return is_scalar($value)
            ? [$value]
            : $value;
    }

    #[Override]
    public function toArray(): array
    {
        return $this->defaults();
    }

    /** Returns the configuration paths. */
    public function configPaths(): ConfigPaths
    {
        return $this->paths;
    }

    /**
     * Resolves source and infra-exclude lists from constructor overrides and YAML base.
     *
     * @param array<string, mixed> $base Raw YAML defaults section
     * @return array{list<string>, list<string>}
     */
    private function resolve(array $base): array
    {
        /** @var list<string> $resolvedSource */
        $resolvedSource = $this->source !== [] ? $this->source : ($base['php.src'] ?? []);

        /** @var list<string> $resolvedInfra */
        $resolvedInfra = $this->infra !== [] ? $this->infra : ($base['infra.exclude'] ?? []);

        return [$resolvedSource, $resolvedInfra];
    }

    /**
     * Parses YAML and computes all defaults with dynamic path derivations.
     *
     * @throws SheriffException
     * @return array<string, scalar|list<scalar>>
     */
    private function defaults(): array
    {
        if (isset($this->cache->value)) {
            /** @var array<string, scalar|list<scalar>> $cached */
            $cached = $this->cache->value;

            return $cached;
        }

        try {
            $yaml = Yaml::parseFile($this->paths->configYaml());
        } catch (ParseException $e) {
            throw new SheriffException(
                sprintf('Failed to parse config "%s": %s', $this->paths->configYaml(), $e->getMessage()),
                0,
                $e,
            );
        }

        if (!is_array($yaml) || !array_key_exists('defaults', $yaml) || !is_array($yaml['defaults'])) {
            throw new SheriffException('Missing "defaults" section in config.yaml');
        }

        /** @var array<string, mixed> $base */
        $base = $yaml['defaults'];

        [$resolvedSource, $resolvedInfra] = $this->resolve($base);

        /** @var array<string, scalar|list<scalar>> $defaults */
        $defaults = array_merge(
            $base,
            $this->dynamic($resolvedSource, $resolvedInfra),
        );
        $this->cache->value = $defaults;

        return $defaults;
    }

    /**
     * Reads dynamic defaults derived from composer.json paths and directory lists.
     *
     * @param list<string> $sources Project source directories
     * @param list<string> $excludes Directories skipped by infrastructure linters
     * @return array<string, scalar|list<scalar>>
     */
    private function dynamic(array $sources, array $excludes): array
    {
        $projectIncludes = (new ProjectDirs($sources))->toList();

        return [
            'php.src' => $sources,
            'infra.exclude' => $excludes,
            'hadolint.ignore' => $excludes,
            'jsonlint.patterns' => ['**/*.json', '**/*.json5', '**/*.jsonc'],
            'markdownlint.ignores' => (new TrailingGlobDirs($excludes))->toList(),
            'phpcs.root_namespace' => (new ComposerRootNamespace($this->paths->composerJson()))->toString(),
            'phpmd.paths' => $sources,
            'phpmetrics.includes' => $projectIncludes,
            'infection.source.directories' => $projectIncludes,
            'shellcheck.ignore_dirs' => $excludes,
            'sonar.sources' => $sources,
            'typos.exclude' => (new TrailingSlashDirs($excludes))->toList(),
            'yamllint.ignore' => array_merge(
                (new TrailingGlobDirs($excludes))->toList(),
                ['.sheriff/**/html/**', '.sheriff/**/coverage-report/**'],
            ),
        ];
    }
}
