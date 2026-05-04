<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Map;

use Haspadar\Sheriff\Chain\Listed;
use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Wraps each part of a Listed source into the same sprintf template.
 *
 * Element-wise counterpart to Formatted. Stays Listed so further pipeline
 * stages can keep iterating; rendering directly is rejected and requires a
 * Reduced op (typically Joined) to collapse the parts into a string.
 *
 * Example:
 *
 *     (new EachFormatted(new ListText($paths), '- %s'))->parts();
 *     // [Formatted(StringText('src'), '- %s'), Formatted(StringText('tests'), '- %s')]
 */
final readonly class EachFormatted implements Listed
{
    /**
     * Initializes with the source list and the sprintf template applied per part.
     *
     * @param Listed $origin Source list whose parts are wrapped one by one
     * @param string $template Sprintf template with zero or one %s placeholder, applied per part
     */
    public function __construct(private Listed $origin, private string $template) {}

    #[Override]
    public function parts(): array
    {
        return array_map(
            fn(Op $part): Op => new Formatted($part, $this->template),
            $this->origin->parts(),
        );
    }

    #[Override]
    public function rendered(): string
    {
        throw new SheriffException(
            'EachFormatted cannot render directly — collapse it via a Reduced op such as Joined',
        );
    }
}
