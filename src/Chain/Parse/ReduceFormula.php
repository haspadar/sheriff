<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Chain\Parse;

use Haspadar\Piqule\Chain\Listed;
use Haspadar\Piqule\Chain\Op;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Settings;
use Override;

/**
 * Builds a Chain\Reduce combinator op from the previous pipeline stages.
 *
 * Used for stages like `Joined("\n")`. When the previous stage is a single
 * Listed op, its `parts()` are unwrapped and passed as the list-of-ops
 * constructor argument; otherwise the previous ops are passed as-is. The
 * remaining template arguments are forwarded as literal strings.
 *
 * Example:
 *
 *     (new ReduceFormula(Joined::class, ['\n']))
 *         ->op([new ListText($paths)], $settings)
 *         ->rendered();
 */
final readonly class ReduceFormula implements Formula
{
    /**
     * Initializes with the combinator class and its raw template arguments.
     *
     * @param class-string<Op> $target FQCN of the Chain\Reduce combinator class
     * @param list<string> $args Literal template arguments passed after the list of ops
     */
    public function __construct(private string $target, private array $args) {}

    #[Override]
    public function op(array $previous, Settings $settings): Op
    {
        if ($previous === []) {
            throw new PiquleException(
                sprintf(
                    'ReduceFormula "%s" requires preceding pipeline stages to combine',
                    $this->target,
                ),
            );
        }

        $className = $this->target;

        return new $className($this->parts($previous), ...$this->args);
    }

    /**
     * Resolves the previous pipeline stages into the list of ops.
     *
     * @param list<Op> $previous
     * @return list<Op>
     */
    private function parts(array $previous): array
    {
        if (count($previous) === 1 && $previous[0] instanceof Listed) {
            return $previous[0]->parts();
        }

        return $previous;
    }
}
