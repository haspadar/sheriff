<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Actions;

use Haspadar\Sheriff\Formula\Action\Action;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Parses a DSL expression string into an ordered list of Action instances.
 */
final readonly class ParsedActions implements Actions
{
    private const int ARGS_MATCH_INDEX = 2;

    /**
     * Initializes with a DSL expression and available action factories.
     *
     * @param string $expression Raw DSL expression to parse into actions
     * @param array<string, callable(string): Action> $actions Action factories keyed by DSL action name
     */
    public function __construct(private string $expression, private array $actions) {}

    #[Override]
    public function all(): array
    {
        preg_match_all(
            '/([a-z_]+)\(([^)]*)\)/',
            $this->expression,
            $matches,
            PREG_SET_ORDER,
        );

        return array_map(function (array $m): Action {
            $name = $m[1];

            if (!array_key_exists($name, $this->actions)) {
                throw new SheriffException(
                    sprintf('Unknown formula action "%s"', $name),
                );
            }

            return ($this->actions[$name])($m[self::ARGS_MATCH_INDEX]);
        }, $matches);
    }
}
