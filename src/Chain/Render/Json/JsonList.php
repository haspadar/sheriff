<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;

/**
 * Renders a ListValue as a json block-style array at the given indent.
 *
 * Empty lists collapse to `[]`. Items render through `JsonOf` and sit at
 * `depth + 1` indentation, comma-separated.
 *
 * Example:
 *
 *     (new JsonList(new ListValue([new StringValue('a')])))->rendered();
 *     // [
 *     // "a"
 *     // ]
 */
final readonly class JsonList implements Rendered
{
    private const string INDENT = '    ';

    /**
     * Initializes with the list to render and the depth at which rendering starts.
     *
     * @param ListValue $value List payload rendered as a json array
     * @param int $depth Indent level applied to each item
     */
    public function __construct(private ListValue $value, private int $depth = 0) {}

    #[Override]
    public function rendered(): string
    {
        if ($this->value->children === []) {
            return '[]';
        }

        $itemPrefix = str_repeat(self::INDENT, $this->depth + 1);
        $closePrefix = str_repeat(self::INDENT, $this->depth);

        $lines = array_map(
            fn(Value $child): string => sprintf(
                '%s%s',
                $itemPrefix,
                (new JsonOf($child, $this->depth + 1))->renderer()->rendered(),
            ),
            $this->value->children,
        );

        return sprintf("[\n%s\n%s]", implode(",\n", $lines), $closePrefix);
    }
}
