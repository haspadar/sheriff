<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

use Haspadar\Sheriff\SheriffException;

/**
 * Tree produced by deep-removing entries from a base tree.
 *
 * The remove spec walks the same shape as the base tree:
 *   * a TreeValue under a key recurses into the matching base subtree;
 *   * a ListValue of strings under a key drops those entries from a base
 *     ListValue, or those keys from a base TreeValue;
 *   * any other shape collision is a configuration error.
 *
 * Removing a key that is absent from the base is silently ignored so that
 * `.sheriff.yaml` stays idempotent across upgrades.
 *
 * Example:
 *
 *     (new RemovedTree($base, $spec))->value();
 */
final readonly class RemovedTree
{
    /**
     * Initializes with the base tree, the remove spec, and the parent path used in error messages.
     *
     * @param TreeValue $base Tree whose entries are filtered down by the spec
     * @param TreeValue $spec Tree describing which subtrees to recurse into and which leaves to drop
     * @param string $path Dot-separated parent path threaded through nested removals for diagnostics; empty at the top level
     */
    public function __construct(
        private TreeValue $base,
        private TreeValue $spec,
        private string $path = '',
    ) {}

    /**
     * Returns the merged tree with spec entries removed from matching leaves.
     *
     * @throws SheriffException
     */
    public function value(): TreeValue
    {
        $entries = $this->base->entries;

        foreach ($this->spec->entries as $key => $instruction) {
            if (!array_key_exists($key, $entries)) {
                continue;
            }

            $entries[$key] = $this->resolved($key, $entries[$key], $instruction);
        }

        return new TreeValue($entries);
    }

    /**
     * Combines the base value with the remove instruction at the same key.
     *
     * @throws SheriffException
     */
    private function resolved(string $key, Value $current, Value $instruction): Value
    {
        if ($instruction instanceof TreeValue && $current instanceof TreeValue) {
            return (new self($current, $instruction, $this->qualified($key)))->value();
        }

        if ($instruction instanceof ListValue && $current instanceof ListValue) {
            return $this->prunedList($current, $instruction, $key);
        }

        if ($instruction instanceof ListValue && $current instanceof TreeValue) {
            return $this->prunedTree($current, $instruction, $key);
        }

        throw new SheriffException(
            sprintf(
                'RemovedTree cannot remove "%s": base is %s but spec is %s; expected matching trees, lists, or a list of keys to drop from a tree',
                $this->qualified($key),
                get_debug_type($current),
                get_debug_type($instruction),
            ),
        );
    }

    /**
     * Returns the source list with every string element listed in the drop list removed.
     *
     * @throws SheriffException
     */
    private function prunedList(ListValue $source, ListValue $drop, string $key): ListValue
    {
        $names = [];

        foreach ($drop->children as $entry) {
            if (!$entry instanceof StringValue) {
                throw new SheriffException(
                    sprintf(
                        'RemovedTree cannot remove from "%s": list drop entries must be strings',
                        $this->qualified($key),
                    ),
                );
            }

            $names[] = $entry->raw;
        }

        return new ListValue(array_values(array_filter(
            $source->children,
            static fn(Value $child): bool => !($child instanceof StringValue) || !in_array($child->raw, $names, true),
        )));
    }

    /**
     * Returns the source tree with every key listed in the drop list removed.
     *
     * @throws SheriffException
     */
    private function prunedTree(TreeValue $source, ListValue $drop, string $key): TreeValue
    {
        $entries = $source->entries;

        foreach ($drop->children as $name) {
            if (!$name instanceof StringValue) {
                throw new SheriffException(
                    sprintf(
                        'RemovedTree cannot remove from "%s": tree-key drop list must contain only strings',
                        $this->qualified($key),
                    ),
                );
            }

            unset($entries[$name->raw]);
        }

        return new TreeValue($entries);
    }

    /** Joins the current path with the given key for a fully-qualified diagnostic. */
    private function qualified(string $key): string
    {
        return $this->path === ''
            ? $key
            : sprintf('%s.%s', $this->path, $key);
    }
}
