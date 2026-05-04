<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Plain;

use Haspadar\Sheriff\Chain\Listed;
use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Exposes a ListValue as a Listed pipeline source of plain element ops.
 *
 * Each element is wrapped into the matching scalar Plain op. Nested lists or
 * trees are not allowed here, since they have no format-neutral text shape.
 * ListText itself refuses direct rendering and requires a Reduced step
 * (typically Joined) further down the pipeline to fold parts into a string.
 *
 * Example:
 *
 *     (new ListText(new ListValue([
 *         new StringValue('src'),
 *         new StringValue('tests'),
 *     ])))->parts(); // [StringText('src'), StringText('tests')]
 */
final readonly class ListText implements Listed
{
    /**
     * Initializes with the list value whose children become pipeline parts.
     *
     * @param ListValue $value Source list whose scalar children are wrapped into Plain ops
     */
    public function __construct(private ListValue $value) {}

    #[Override]
    public function parts(): array
    {
        return array_map(
            fn(Value $child): Op => $this->asText($child),
            $this->value->children,
        );
    }

    #[Override]
    public function rendered(): string
    {
        throw new SheriffException(
            'ListText cannot render directly — collapse it via a Reduced op such as Joined',
        );
    }

    /**
     * Wraps a single Value child into the matching scalar Plain op.
     *
     * @throws SheriffException
     */
    private function asText(Value $child): Op
    {
        return match (true) {
            $child instanceof BoolValue => new BoolText($child),
            $child instanceof IntValue => new IntText($child),
            $child instanceof FloatValue => new FloatText($child),
            $child instanceof StringValue => new StringText($child),
            default => throw new SheriffException(
                sprintf(
                    'ListText only accepts scalar children, got "%s"',
                    $child::class,
                ),
            ),
        };
    }
}
