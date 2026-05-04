<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\RawValue;
use Haspadar\Sheriff\Settings\Value\ScalarValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use TypeError;

/**
 * Translates the `override` section of `.sheriff.yaml` into Patch instances.
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
     * @throws SheriffException|TypeError
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
     * @throws SheriffException|TypeError
     */
    private function patchOf(string $key, mixed $raw): Patch
    {
        $value = (new RawValue($raw))->value();

        return match (true) {
            $value instanceof ScalarValue => new OverrideScalar($key, $value),
            $value instanceof ListValue => new OverrideList($key, $value),
            $value instanceof TreeValue => new OverrideTree($key, $value),
            default => throw new SheriffException(
                sprintf('Override "%s" expects a scalar, list, or mapping', $key),
            ),
        };
    }
}
