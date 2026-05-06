<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\AppendedTree;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Deep-appends a tree of extra entries onto a base tree.
 *
 * Walks both trees in parallel: missing keys are added as-is, matching trees
 * recurse, matching lists are concatenated. Any other shape collision raises a
 * SheriffException so misconfigured `.sheriff.yaml` files fail loudly.
 *
 * An empty ListValue base is accepted as an empty tree because the YAML
 * default `key: {}` collapses to `[]` in PHP/Symfony, and RawValue wraps
 * it as ListValue rather than TreeValue.
 *
 * Example:
 *
 *     new AppendTree('phpstan.parameters', new TreeValue([
 *         'haspadar' => new TreeValue([
 *             'afferentCoupling' => new TreeValue([
 *                 'excludedClasses' => new ListValue([new StringValue('\\App\\My')]),
 *             ]),
 *         ]),
 *     ]));
 */
final readonly class AppendTree implements Patch
{
    /**
     * Initializes with the target key and the entries to merge into the base tree.
     *
     * @param string $key Configuration key whose tree value is extended
     * @param TreeValue $extra Tree whose entries add new keys or extend matching list/tree leaves
     */
    public function __construct(private string $key, private TreeValue $extra) {}

    #[Override]
    public function key(): string
    {
        return $this->key;
    }

    #[Override]
    public function applied(Value $base): Value
    {
        if ($base instanceof ListValue && $base->children === []) {
            return $this->extra;
        }

        if (!$base instanceof TreeValue) {
            throw new TypeError(
                sprintf('AppendTree expects TreeValue at "%s"', $this->key),
            );
        }

        return (new AppendedTree($base, $this->extra))->value();
    }
}
