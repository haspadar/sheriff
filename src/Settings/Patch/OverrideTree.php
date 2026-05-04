<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\MergedTree;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Deep-merges an override tree into the base tree at the given key.
 *
 * Example:
 *
 *     new OverrideTree('phpstan.parameters', new TreeValue([
 *         'haspadar' => new TreeValue([
 *             'afferentCoupling' => new TreeValue([
 *                 'ignoreAbstract' => new BoolValue(true),
 *             ]),
 *         ]),
 *     ]));
 */
final readonly class OverrideTree implements Patch
{
    /**
     * Initializes with the target key and the override tree to merge in.
     *
     * @param string $key Configuration key whose tree value is merged
     * @param TreeValue $value Tree carrying the entries to override on the base
     */
    public function __construct(private string $key, private TreeValue $value) {}

    #[Override]
    public function key(): string
    {
        return $this->key;
    }

    #[Override]
    public function applied(Value $base): Value
    {
        if (!$base instanceof TreeValue) {
            throw new TypeError(
                sprintf('OverrideTree expects TreeValue at "%s"', $this->key),
            );
        }

        return (new MergedTree($base, $this->value))->value();
    }
}
