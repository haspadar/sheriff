<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use JsonException;
use Override;

/**
 * Renders a TreeValue as a json block-style object at the given indent.
 *
 * Empty trees collapse to `{}`. Keys are rendered through `JsonString`,
 * values through `JsonOf`. Entries are separated by `,\n` and indented
 * by `depth + 1`.
 *
 * Example:
 *
 *     (new JsonTree(new TreeValue(['a' => new IntValue(1)])))->rendered();
 *     // {
 *     // "a": 1
 *     // }
 */
final readonly class JsonTree implements Rendered
{
    private const string INDENT = '    ';

    /**
     * Initializes with the tree to render and the depth at which rendering starts.
     *
     * @param TreeValue $value Tree payload rendered as a json object
     * @param int $depth Indent level applied to nested entries
     */
    public function __construct(private TreeValue $value, private int $depth = 0) {}

    #[Override]
    public function rendered(): string
    {
        if ($this->value->entries === []) {
            return '{}';
        }

        $entryPrefix = str_repeat(self::INDENT, $this->depth + 1);
        $closePrefix = str_repeat(self::INDENT, $this->depth);
        $lines = [];

        foreach ($this->value->entries as $key => $child) {
            $lines[] = sprintf(
                '%s%s: %s',
                $entryPrefix,
                $this->keyLiteral($key),
                (new JsonOf($child, $this->depth + 1))->renderer()->rendered(),
            );
        }

        return sprintf("{\n%s\n%s}", implode(",\n", $lines), $closePrefix);
    }

    /**
     * Encodes the key as a json string literal.
     *
     * @throws JsonException
     */
    private function keyLiteral(string $key): string
    {
        return (new JsonString(new StringValue($key)))->rendered();
    }
}
