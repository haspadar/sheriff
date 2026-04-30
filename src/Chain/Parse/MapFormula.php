<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Chain\Parse;

use Haspadar\Piqule\Chain\Op;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Settings;
use Override;

/**
 * Builds a Chain\Map decorator op on top of the previous pipeline stage.
 *
 * Used for stages like `Formatted("- %s")` or `EachFormatted("- %s")`. The
 * head op produced by the previous stage becomes the first constructor
 * argument; the remaining template arguments are passed through verbatim as
 * literal strings. Type mismatches surface as PHP TypeErrors so misuse is
 * loud at render time.
 *
 * Example:
 *
 *     (new MapFormula(Formatted::class, ['- %s']))
 *         ->op([new IntText(new IntValue(9))], $settings)
 *         ->rendered(); // "- 9"
 */
final readonly class MapFormula implements Formula
{
    /**
     * Initializes with the decorator class and its raw template arguments.
     *
     * @param class-string<Op> $target FQCN of the Chain\Map decorator class
     * @param list<string> $args Literal template arguments passed after the previous op
     */
    public function __construct(private string $target, private array $args) {}

    #[Override]
    public function op(array $previous, Settings $settings): Op
    {
        if (count($previous) !== 1) {
            throw new PiquleException(
                sprintf(
                    'MapFormula "%s" expects exactly one preceding op, got %d',
                    $this->target,
                    count($previous),
                ),
            );
        }

        $className = $this->target;

        return new $className($previous[0], ...$this->args);
    }
}
