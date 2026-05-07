<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\RawValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use TypeError;

/**
 * Translates the `remove` section of `.sheriff.yaml` into Patch instances.
 *
 * A list payload becomes a `RemoveList` that drops elements from the base
 * list at the same key. A mapping payload becomes a `RemoveTree` whose
 * recursive walk filters nested trees and lists in lockstep with the base.
 *
 * Example:
 *
 *     (new RemovePatches([
 *         'phpstan.checked_exceptions' => ['\\Throwable'],
 *         'phpstan.parameters' => [
 *             'haspadar' => [
 *                 'afferentCoupling' => [
 *                     'excludedClasses' => ['\\App\\Foo'],
 *                 ],
 *             ],
 *         ],
 *     ]))->patches();
 */
final readonly class RemovePatches
{
    /**
     * Initializes with the raw `remove` section from the yaml file.
     *
     * @param array<string, mixed> $section Raw yaml mapping under `remove`
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
     * Builds the matching Remove patch from the runtime type of the payload.
     *
     * @throws SheriffException|TypeError
     */
    private function patchOf(string $key, mixed $raw): Patch
    {
        $value = (new RawValue($raw))->value();

        return match (true) {
            $value instanceof ListValue => new RemoveList($key, $value),
            $value instanceof TreeValue => new RemoveTree($key, $value),
            default => throw new SheriffException(
                sprintf('Remove "%s" expects a list or mapping', $key),
            ),
        };
    }
}
