<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Tree produced by deep-merging an override tree onto a base tree.
 *
 * Override entries replace base entries at every leaf. When both sides hold
 * a TreeValue at the same key, the merge recurses into the nested trees.
 *
 * Example:
 *
 *     (new MergedTree($base, $override))->value();
 */
final readonly class MergedTree
{
    /**
     * Initializes with the base tree and the tree carrying the overrides.
     *
     * @param TreeValue $base Tree whose entries are taken when the override leaves them untouched
     * @param TreeValue $override Tree whose entries replace or recurse into matching base entries
     */
    public function __construct(private TreeValue $base, private TreeValue $override) {}

    /** Returns the merged tree where override entries win over base entries. */
    public function value(): TreeValue
    {
        $entries = $this->base->entries;

        foreach ($this->override->entries as $key => $value) {
            $entries[$key] = array_key_exists($key, $entries)
                ? $this->resolved($entries[$key], $value)
                : $value;
        }

        return new TreeValue($entries);
    }

    private function resolved(Value $first, Value $second): Value
    {
        return $first instanceof TreeValue && $second instanceof TreeValue
            ? (new self($first, $second))->value()
            : $second;
    }
}
