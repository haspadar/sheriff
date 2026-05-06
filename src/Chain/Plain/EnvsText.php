<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Plain;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Renders a TreeValue of CI environment variables as a GitHub Actions step.
 *
 * Each entry is `VAR_NAME => "shell command"`. The variable name must match
 * `^[A-Za-z_][A-Za-z0-9_]*$`. Empty trees render as the empty string so the
 * surrounding workflow yaml stays well-formed.
 *
 * Example:
 *
 *     (new EnvsText(
 *         new TreeValue(['COMPOSER_ROOT_VERSION' => new StringValue('git describe')]),
 *         ' ',
 *     ))->rendered();
 */
final readonly class EnvsText implements Op
{
    /**
     * Initializes with the envs payload and the YAML indent prefix.
     *
     * @param Value $envs TreeValue of env names to shell commands; an empty ListValue is also accepted because YAML `{}` parses as `[]`
     * @param string $indent Raw indentation prefix used for nested YAML output
     */
    public function __construct(private Value $envs, private string $indent) {}

    #[Override]
    public function rendered(): string
    {
        $entries = $this->entries();

        if ($entries === []) {
            return '';
        }

        $lines = [
            sprintf('%s    git fetch --tags --unshallow 2>/dev/null || git fetch --tags', $this->indent),
        ];

        foreach ($entries as $name => $value) {
            $lines[] = sprintf(
                '%s    echo "%s=$(%s)" >> "$GITHUB_ENV"',
                $this->indent,
                $name,
                $this->command($name, $value),
            );
        }

        return sprintf(
            "%s- name: Set environment variables\n%s  run: |\n%s",
            $this->indent,
            $this->indent,
            implode("\n", $lines),
        );
    }

    /**
     * Resolves the envs payload to its key-value entries.
     *
     * @throws SheriffException
     * @return array<string, Value>
     */
    private function entries(): array
    {
        if ($this->envs instanceof TreeValue) {
            return $this->envs->entries;
        }

        if ($this->envs instanceof ListValue && $this->envs->children === []) {
            return [];
        }

        throw new SheriffException(
            sprintf('EnvsText requires a TreeValue payload, got %s', get_debug_type($this->envs)),
        );
    }

    /**
     * Validates the env name and unwraps the StringValue command.
     *
     * @throws SheriffException
     */
    private function command(string $name, Value $value): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new SheriffException(
                sprintf('Invalid environment variable name "%s"', $name),
            );
        }

        if (!$value instanceof StringValue) {
            throw new SheriffException(
                sprintf('Environment variable "%s" must hold a StringValue command', $name),
            );
        }

        if (preg_match('/["\\\\]/', $value->raw) === 1) {
            throw new SheriffException(
                sprintf('Environment variable "%s" command must not contain " or \\ characters', $name),
            );
        }

        return $value->raw;
    }
}
