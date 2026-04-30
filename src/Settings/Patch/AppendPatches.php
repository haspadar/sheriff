<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Settings\Patch;

use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Patch;
use Haspadar\Piqule\Settings\Value\ListValue;
use Haspadar\Piqule\Settings\Value\RawValue;
use Haspadar\Piqule\Settings\Value\TreeValue;
use TypeError;

/**
 * Translates the `append` section of `.piqule.yaml` into Patch instances.
 *
 * Example:
 *
 *     (new AppendPatches([
 *         'infra.exclude' => ['dist'],
 *         'phpstan.parameters' => ['ignoreErrors' => ['#new#']],
 *     ]))->patches();
 */
final readonly class AppendPatches
{
    /**
     * Initializes with the raw `append` section from the yaml file.
     *
     * @param array<string, mixed> $section Raw yaml mapping under `append`
     */
    public function __construct(private array $section) {}

    /**
     * Returns the list of Patch objects derived from the section.
     *
     * @throws PiquleException|TypeError
     * @return list<Patch>
     */
    public function patches(): array
    {
        $patches = [];

        /** @var mixed $raw */
        foreach ($this->section as $key => $raw) {
            $patches[] = $this->patchOf($key, $raw);
        }

        return $patches;
    }

    /**
     * Builds a single append patch matching the runtime type of the payload.
     *
     * @throws PiquleException|TypeError
     */
    private function patchOf(string $key, mixed $raw): Patch
    {
        $value = (new RawValue($raw))->value();

        return match (true) {
            $value instanceof ListValue => new AppendList($key, $value),
            $value instanceof TreeValue => new AppendTree($key, $value),
            default => throw new PiquleException(
                sprintf('Append "%s" expects a list or mapping', $key),
            ),
        };
    }
}
