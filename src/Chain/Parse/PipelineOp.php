<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Chain\Parse;

use Haspadar\Piqule\Chain\Op;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Settings;
use Override;

/**
 * Folds an ordered list of Formula stages into a single rendered string.
 *
 * The first formula starts with no previous stages; each subsequent formula
 * receives the op produced by its predecessor as its sole previous input.
 * The op produced by the last formula is rendered to give the final result.
 *
 * Example:
 *
 *     (new PipelineOp([
 *         new SourceFormula(ListText::class, ['phpstan.paths']),
 *         new MapFormula(EachFormatted::class, ['- %s']),
 *         new ReduceFormula(Joined::class, ["\n"]),
 *     ], $settings))->rendered();
 *     // "- src\n- tests"
 */
final readonly class PipelineOp implements Op
{
    /**
     * Initializes with the ordered formulas and the settings context.
     *
     * @param list<Formula> $formulas Pipeline formulas in source-to-tail order, must not be empty
     * @param Settings $settings Settings context shared by every stage
     */
    public function __construct(private array $formulas, private Settings $settings) {}

    #[Override]
    public function rendered(): string
    {
        if ($this->formulas === []) {
            throw new PiquleException(
                'PipelineOp requires at least one formula to render',
            );
        }

        $previous = [];

        foreach ($this->formulas as $formula) {
            $previous = [$formula->op($previous, $this->settings)];
        }

        return $previous[0]->rendered();
    }
}
