<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Patch;

use Haspadar\Sheriff\Settings\Patch;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\RemovedTree;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Deep-removes entries from a tree configuration value.
 *
 * Walks the spec in parallel with the base tree: matching subtrees recurse,
 * a list-of-strings spec drops elements from a base list (or keys from a
 * base tree). Missing keys are tolerated so the patch stays idempotent.
 *
 * An empty ListValue base is accepted as an empty tree because the YAML
 * default `key: {}` collapses to `[]` in PHP/Symfony, and RawValue wraps
 * it as ListValue rather than TreeValue.
 *
 * Example:
 *
 *     new RemoveTree('phpstan.parameters', new TreeValue([
 *         'haspadar' => new TreeValue([
 *             'afferentCoupling' => new TreeValue([
 *                 'excludedClasses' => new ListValue([new StringValue('\\App\\Foo')]),
 *             ]),
 *         ]),
 *     ]));
 */
final readonly class RemoveTree implements Patch
{
    /**
     * Initializes with the target key and the spec describing what to remove.
     *
     * @param string $key Configuration key whose tree value is filtered down
     * @param TreeValue $spec Tree describing which subtrees to recurse into and which leaves to drop
     */
    public function __construct(private string $key, private TreeValue $spec) {}

    #[Override]
    public function key(): string
    {
        return $this->key;
    }

    #[Override]
    public function applied(Value $base): Value
    {
        if ($base instanceof ListValue && $base->children === []) {
            return new TreeValue([]);
        }

        if (!$base instanceof TreeValue) {
            throw new TypeError(
                sprintf('RemoveTree expects TreeValue at "%s"', $this->key),
            );
        }

        return (new RemovedTree($base, $this->spec))->value();
    }
}
