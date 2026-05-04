<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;

/**
 * Renders a ListValue as a neon flow-style sequence.
 *
 * Example:
 *
 *     (new NeonList(new ListValue([new StringValue('src')])))->rendered();
 *     // [ "src" ]
 */
final readonly class NeonList implements Rendered
{
    /**
     * Initializes with the list to render.
     *
     * @param ListValue $value List payload rendered as a neon flow sequence
     */
    public function __construct(private ListValue $value) {}

    #[Override]
    public function rendered(): string
    {
        if ($this->value->children === []) {
            return '[]';
        }

        $parts = array_map(
            static fn(Value $child): string => (new NeonOf($child))->renderer()->rendered(),
            $this->value->children,
        );

        return sprintf('[%s]', implode(', ', $parts));
    }
}
