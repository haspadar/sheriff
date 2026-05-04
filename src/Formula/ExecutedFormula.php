<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula;

use Haspadar\Sheriff\Formula\Actions\Actions;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Evaluates a pipeline of actions and returns the resulting scalar string.
 */
final readonly class ExecutedFormula implements Formula
{
    /**
     * Initializes with a sequence of actions to evaluate.
     *
     * @param Actions $actions Ordered actions applied in a pipeline to reduce to a scalar
     */
    public function __construct(private Actions $actions) {}

    #[Override]
    public function result(): string
    {
        $args = new ListArgs([]);

        foreach ($this->actions->all() as $action) {
            $args = $action->transformed($args);
        }

        $values = $args->values();

        return match (count($values)) {
            0 => '',
            1 => (string) $values[0],
            default => throw new SheriffException(
                'Formula must reduce to a single value, use join() to reduce list',
            ),
        };
    }
}
