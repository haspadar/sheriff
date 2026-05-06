<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Builds a pipeline source op from a settings key plus optional literals.
 *
 * Used for pipeline heads like `BoolText(phpstan.cli)`,
 * `NeonTree(phpstan.parameters)`, or `EnvsText(envs, " ")`. The first
 * argument is treated as a dotted settings key; the resolved Value is fed into
 * the target class's constructor as the first ctor argument, followed by any
 * remaining template arguments as literal strings. Type mismatches surface
 * naturally as PHP TypeErrors, making misconfigured templates fail loudly at
 * render time. Works for any Chain class whose constructor takes a Value
 * followed by zero or more strings, regardless of namespace.
 *
 * Example:
 *
 *     (new SourceFormula(IntText::class, ['phpstan.level']))
 *         ->op([], $settings)
 *         ->rendered(); // "9"
 */
final readonly class SourceFormula implements Formula
{
    /**
     * Initializes with the source class and its raw template arguments.
     *
     * @param class-string<Op> $target FQCN of the source class to instantiate
     * @param list<string> $args Raw template arguments — the first is the settings key, the rest are passed as ctor literals
     */
    public function __construct(private string $target, private array $args) {}

    #[Override]
    public function op(array $previous, Settings $settings): Op
    {
        if ($this->args === []) {
            throw new SheriffException(
                sprintf(
                    'SourceFormula "%s" expects at least one settings key',
                    $this->target,
                ),
            );
        }

        $key = $this->args[0];

        if (!$settings->has($key)) {
            throw new SheriffException(
                sprintf(
                    'SourceFormula "%s" cannot find settings key "%s"',
                    $this->target,
                    $key,
                ),
            );
        }

        $value = $settings->value($key);
        $className = $this->target;

        return new $className($value, ...array_slice($this->args, 1));
    }
}
