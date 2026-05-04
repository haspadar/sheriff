<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\SheriffException;

/**
 * Formula stages parsed from a template pipeline string.
 *
 * Example:
 *
 *     (new PipelineFormulas('ListText(phpstan.paths)|Joined(", ")'))->formulas();
 */
final readonly class PipelineFormulas
{
    /**
     * Initializes with the raw pipeline expression.
     *
     * @param string $pipeline Text between `<<` and `>>`
     */
    public function __construct(private string $pipeline) {}

    /**
     * Returns formulas in source-to-tail order.
     *
     * @throws SheriffException
     * @return list<Formula>
     */
    public function formulas(): array
    {
        return array_map(
            fn(string $text): Formula => (new PipelineFormula($text))->formula(),
            (new SeparatedTexts($this->pipeline, '|'))->values(),
        );
    }
}
