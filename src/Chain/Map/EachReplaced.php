<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Map;

use Haspadar\Sheriff\Chain\Listed;
use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Replaces every occurrence of a needle in each part of a Listed source.
 *
 * Element-wise counterpart to Replaced. Stays Listed so further pipeline
 * stages can keep iterating; rendering directly is rejected and requires a
 * Reduced op (typically Joined) to collapse the parts into a string.
 *
 * Example:
 *
 *     (new EachReplaced(new ListText($versions), '.', 'x'))->parts();
 *     // [Replaced(StringText('8.3'), '.', 'x'), Replaced(StringText('8.4'), '.', 'x')]
 */
final readonly class EachReplaced implements Listed
{
    /**
     * Initializes with the source list and the replacement pair applied per part.
     *
     * @param Listed $origin Source list whose parts are rewritten one by one
     * @param string $needle Substring searched in each rendered part
     * @param string $replacement Substring inserted in place of every needle
     */
    public function __construct(
        private Listed $origin,
        private string $needle,
        private string $replacement,
    ) {}

    #[Override]
    public function parts(): array
    {
        return array_map(
            fn(Op $part): Op => new Replaced($part, $this->needle, $this->replacement),
            $this->origin->parts(),
        );
    }

    #[Override]
    public function rendered(): string
    {
        throw new SheriffException(
            'EachReplaced cannot render directly — collapse it via a Reduced op such as Joined',
        );
    }
}
