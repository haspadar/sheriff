<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Chain\Parse;

use Haspadar\Piqule\Chain\Op;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Settings;
use Override;

/**
 * Builds a pipeline source op from a settings key.
 *
 * Used for pipeline heads like `BoolText(phpstan.cli)` or
 * `NeonTree(phpstan.parameters)`. The single argument is treated as a dotted
 * settings key; the resolved Value is fed into the target class's
 * constructor. Type mismatches surface naturally as PHP TypeErrors,
 * making misconfigured templates fail loudly at render time. Works for any
 * Chain class whose constructor takes one Value, regardless of namespace
 * (Chain\Plain or Chain\Render\<Format>).
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
     * @param list<string> $args Raw template arguments, exactly one settings key for sources
     */
    public function __construct(private string $target, private array $args) {}

    #[Override]
    public function op(array $previous, Settings $settings): Op
    {
        if (count($this->args) !== 1) {
            throw new PiquleException(
                sprintf(
                    'SourceFormula "%s" expects exactly one settings key, got %d arguments',
                    $this->target,
                    count($this->args),
                ),
            );
        }

        $key = $this->args[0];

        if (!$settings->has($key)) {
            throw new PiquleException(
                sprintf(
                    'SourceFormula "%s" cannot find settings key "%s"',
                    $this->target,
                    $key,
                ),
            );
        }

        $value = $settings->value($key);
        $className = $this->target;

        return new $className($value);
    }
}
