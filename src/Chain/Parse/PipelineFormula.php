<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\SheriffException;

/**
 * One textual `Name(args)` stage from a template pipeline.
 *
 * Example:
 *
 *     (new PipelineFormula('ListText(phpstan.paths)'))->formula();
 */
final readonly class PipelineFormula
{
    private const int MATCH_NAME = 1;

    private const int MATCH_ARGS = 2;

    /**
     * Initializes with one formula call.
     *
     * @param string $text Formula call text
     */
    public function __construct(private string $text) {}

    /**
     * Returns the executable Formula represented by this text.
     *
     * @throws SheriffException
     */
    public function formula(): Formula
    {
        if (preg_match('/^\s*([A-Z][A-Za-z0-9]*)\s*\((.*)\)\s*$/s', $this->text, $match) !== 1) {
            throw new SheriffException(sprintf('Invalid pipeline formula "%s"', $this->text));
        }

        return (new FormulaTarget(
            $match[self::MATCH_NAME],
            (new FormulaArgs($match[self::MATCH_ARGS]))->values(),
        ))->formula();
    }
}
