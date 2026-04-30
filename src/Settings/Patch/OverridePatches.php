<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Settings\Patch;

use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Patch;
use Haspadar\Piqule\Settings\Value\ListValue;
use Haspadar\Piqule\Settings\Value\RawValue;
use Haspadar\Piqule\Settings\Value\ScalarValue;
use Haspadar\Piqule\Settings\Value\TreeValue;
use TypeError;

/**
 * Translates the `override` section of `.piqule.yaml` into Patch instances.
 *
 * Example:
 *
 *     (new OverridePatches([
 *         'phpstan.level' => 8,
 *         'phpstan.parameters' => ['haspadar' => ['ignoreAbstract' => true]],
 *     ]))->patches();
 */
final readonly class OverridePatches
{
    /**
     * Initializes with the raw `override` section from the yaml file.
     *
     * @param array<string, mixed> $section Raw yaml mapping under `override`
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
     * Builds a single override patch matching the runtime type of the payload.
     *
     * @throws PiquleException|TypeError
     */
    private function patchOf(string $key, mixed $raw): Patch
    {
        $value = (new RawValue($raw))->value();

        return match (true) {
            $value instanceof ScalarValue => new OverrideScalar($key, $value),
            $value instanceof ListValue => new OverrideList($key, $value),
            $value instanceof TreeValue => new OverrideTree($key, $value),
            default => throw new PiquleException(
                sprintf('Override "%s" expects a scalar, list, or mapping', $key),
            ),
        };
    }
}
