<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\Chain\Op;
use InvalidArgumentException;

/**
 * Formula class resolved from a template formula name.
 *
 * Example:
 *
 *     (new FormulaTarget('ListText', ['phpstan.paths']))->formula();
 */
final readonly class FormulaTarget
{
    private const array CUSTOM_NAMES = ['EnabledTools', 'JoinedLists', 'PhpunitTestsuites'];

    /**
     * Initializes with a PascalCase formula name and its raw arguments.
     *
     * @param string $name Formula name from the template
     * @param list<string> $args Formula arguments
     */
    public function __construct(private string $name, private array $args) {}

    /**
     * Returns the Formula implementation for the resolved Chain class.
     *
     * @throws InvalidArgumentException
     */
    public function formula(): Formula
    {
        if (in_array($this->name, self::CUSTOM_NAMES, true)) {
            return $this->customFormula();
        }

        foreach ($this->candidates() as $candidate) {
            /** @var class-string<Op> $target */
            $target = sprintf('%s%s', $candidate['namespace'], $this->name);

            if (!class_exists($target) || !is_subclass_of($target, Op::class)) {
                continue;
            }

            return match ($candidate['kind']) {
                'source' => new SourceFormula($target, $this->args),
                'map' => new MapFormula($target, $this->args),
                'reduce' => new ReduceFormula($target, $this->args),
            };
        }

        throw new InvalidArgumentException(sprintf('Unknown pipeline formula "%s"', $this->name));
    }

    /**
     * Returns the dedicated formula for one of the CUSTOM_NAMES entries.
     *
     * @throws InvalidArgumentException
     */
    private function customFormula(): Formula
    {
        return match ($this->name) {
            'EnabledTools' => new EnabledToolsFormula($this->args),
            'JoinedLists' => new JoinedListsFormula($this->args),
            'PhpunitTestsuites' => new PhpunitTestsuitesFormula($this->args),
            default => throw new InvalidArgumentException(sprintf('Unknown custom formula "%s"', $this->name)),
        };
    }

    /**
     * Returns namespaces in the resolution order.
     *
     * @return list<array{namespace: string, kind: 'source'|'map'|'reduce'}>
     */
    private function candidates(): array
    {
        return [
            ['namespace' => 'Haspadar\\Sheriff\\Chain\\Render\\Neon\\', 'kind' => 'source'],
            ['namespace' => 'Haspadar\\Sheriff\\Chain\\Render\\Json\\', 'kind' => 'source'],
            ['namespace' => 'Haspadar\\Sheriff\\Chain\\Render\\Xml\\', 'kind' => 'source'],
            ['namespace' => 'Haspadar\\Sheriff\\Chain\\Render\\Php\\', 'kind' => 'source'],
            ['namespace' => 'Haspadar\\Sheriff\\Chain\\Map\\', 'kind' => 'map'],
            ['namespace' => 'Haspadar\\Sheriff\\Chain\\Reduce\\', 'kind' => 'reduce'],
            ['namespace' => 'Haspadar\\Sheriff\\Chain\\Plain\\', 'kind' => 'source'],
        ];
    }
}
