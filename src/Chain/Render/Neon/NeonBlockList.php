<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;
use TypeError;

/**
 * Renders a ListValue as a neon block-style sequence at the given indent.
 *
 * Empty lists collapse to `[]`. Items render through NeonOf and sit at
 * `depth + 1` indentation, prefixed with `- `.
 *
 * Example:
 *
 *     (new NeonBlockList(new ListValue([new StringValue('Foo')]), 1))->rendered();
 *     // \n - Foo
 */
final readonly class NeonBlockList implements Rendered
{
    private const string INDENT = '    ';

    /**
     * Initializes with the list to render and the depth at which rendering starts.
     *
     * @param ListValue $value List payload rendered as a neon block sequence
     * @param int $depth Indent level applied to each item
     */
    public function __construct(private ListValue $value, private int $depth = 0) {}

    #[Override]
    public function rendered(): string
    {
        if ($this->value->children === []) {
            return '[]';
        }

        $prefix = str_repeat(self::INDENT, $this->depth + 1);
        $lines = array_map(
            fn(Value $child): string => sprintf('%s- %s', $prefix, $this->itemFor($child)),
            $this->value->children,
        );

        return sprintf("\n%s", implode("\n", $lines));
    }

    /**
     * Renders a single list item, preferring bare strings inside block lists.
     *
     * @throws TypeError
     */
    private function itemFor(Value $child): string
    {
        return $child instanceof StringValue
            ? (new NeonBareString($child))->rendered()
            : (new NeonOf($child))->renderer()->rendered();
    }
}
