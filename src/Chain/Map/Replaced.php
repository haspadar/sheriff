<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Map;

use Haspadar\Sheriff\Chain\Mapped;
use Haspadar\Sheriff\Chain\Op;
use Override;

/**
 * Replaces every occurrence of a needle in the rendered output.
 *
 * Example:
 *
 *     (new Replaced(new StringText(new StringValue('8.3')), '.', 'x'))->rendered();
 *     // "8x3"
 */
final readonly class Replaced implements Mapped
{
    /**
     * Initializes with the source op and the replacement pair.
     *
     * @param Op $origin Source op whose rendered output is rewritten
     * @param string $needle Substring searched in the rendered output
     * @param string $replacement Substring inserted in place of every needle
     */
    public function __construct(
        private Op $origin,
        private string $needle,
        private string $replacement,
    ) {}

    #[Override]
    public function rendered(): string
    {
        return str_replace($this->needle, $this->replacement, $this->origin->rendered());
    }
}
