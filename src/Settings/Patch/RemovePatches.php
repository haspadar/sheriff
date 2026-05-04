<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\RawValue;
use Haspadar\Sheriff\SheriffException;
use TypeError;

/**
 * Translates the `remove` section of `.sheriff.yaml` into Patch instances.
 *
 * Yaml input always produces RemoveList. RemoveTree (drop named keys from a
 * tree) is constructed programmatically because the parser cannot tell a
 * list of items from a list of key names without knowing the configured
 * key's default type.
 *
 * Example:
 *
 *     (new RemovePatches([
 *         'phpstan.checked_exceptions' => ['\\Throwable'],
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
     * Builds a RemoveList patch from a yaml list payload.
     *
     * @throws SheriffException|TypeError
     */
    private function patchOf(string $key, mixed $raw): Patch
    {
        $value = (new RawValue($raw))->value();

        if (!$value instanceof ListValue) {
            throw new SheriffException(
                sprintf('Remove "%s" expects a list of items to drop', $key),
            );
        }

        return new RemoveList($key, $value);
    }
}
