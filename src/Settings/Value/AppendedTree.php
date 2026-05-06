<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

use Haspadar\Sheriff\SheriffException;

/**
 * Tree produced by deep-appending an extra tree onto a base tree.
 *
 * For every key in the extra tree:
 *   * if the base lacks the key, the extra value is added as-is;
 *   * if both sides hold a TreeValue, the merge recurses;
 *   * if both sides hold a ListValue, the extra entries are appended after the base entries;
 *   * any other type collision is a configuration error.
 *
 * Example:
 *
 *     (new AppendedTree($base, $extra))->value();
 */
final readonly class AppendedTree
{
    /**
     * Initializes with the base tree and the tree carrying the appended entries.
     *
     * @param TreeValue $base Tree whose entries are kept untouched when the extra leaves them out
     * @param TreeValue $extra Tree whose entries either add new keys or extend matching list/tree leaves
     */
    public function __construct(private TreeValue $base, private TreeValue $extra) {}

    /**
     * Returns the merged tree with extra entries appended into matching leaves.
     *
     * @throws SheriffException
     */
    public function value(): TreeValue
    {
        $entries = $this->base->entries;

        foreach ($this->extra->entries as $key => $value) {
            $entries[$key] = array_key_exists($key, $entries)
                ? $this->resolved($key, $entries[$key], $value)
                : $value;
        }

        return new TreeValue($entries);
    }

    /**
     * Combines two values that share the same key during the merge.
     *
     * @throws SheriffException
     */
    private function resolved(string $key, Value $first, Value $second): Value
    {
        if ($first instanceof TreeValue && $second instanceof TreeValue) {
            return (new self($first, $second))->value();
        }

        if ($first instanceof ListValue && $second instanceof ListValue) {
            return new ListValue([...$first->children, ...$second->children]);
        }

        throw new SheriffException(
            sprintf(
                'AppendedTree cannot merge "%s": base is %s but extra is %s; expected matching trees or lists',
                $key,
                get_debug_type($first),
                get_debug_type($second),
            ),
        );
    }
}
