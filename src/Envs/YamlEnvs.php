<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Envs;

use Haspadar\Sheriff\SheriffException;
use Override;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses the envs section from a .sheriff.yaml file.
 */
final readonly class YamlEnvs implements Envs
{
    /**
     * Initializes with the path to a .sheriff.yaml file.
     *
     * @param string $path Absolute path to the .sheriff.yaml file to read
     */
    public function __construct(private string $path) {}

    #[Override]
    public function vars(): array
    {
        try {
            /** @var mixed $yaml */
            $yaml = Yaml::parseFile($this->path);
        } catch (ParseException $e) {
            throw new SheriffException(
                sprintf('Failed to parse "%s": %s', $this->path, $e->getMessage()),
                0,
                $e,
            );
        }

        if (!is_array($yaml)) {
            throw new SheriffException(
                sprintf('Expected a mapping in "%s", got %s', $this->path, get_debug_type($yaml)),
            );
        }

        /** @var mixed $envs */
        $envs = $yaml['envs'] ?? [];

        if (!is_array($envs)) {
            throw new SheriffException(
                sprintf('Invalid "envs" section in "%s": expected a mapping', $this->path),
            );
        }

        /** @var array<string, string> $vars */
        $vars = [];

        foreach ($envs as $name => $command) {
            if (!is_string($name) || !is_string($command)) {
                throw new SheriffException(
                    sprintf('Each entry in "envs" must be string => string in "%s"', $this->path),
                );
            }

            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
                throw new SheriffException(
                    sprintf('Invalid environment variable name "%s" in "%s"', $name, $this->path),
                );
            }

            $vars[$name] = $command;
        }

        return $vars;
    }
}
