<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Xml;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Renders PHPUnit testsuite blocks from a names-to-subdirs tree and base dirs.
 *
 * Each entry of the tree becomes one `<testsuite>` block whose name is the
 * entry key. The directory list is the cartesian product of the base
 * directories from `php.tests` and the subdirectories under that suite.
 * An empty tree yields a single `default` suite covering every base directory.
 *
 * Example:
 *
 *     (new PhpunitTestsuites(
 *         new TreeValue(['unit' => new ListValue([new StringValue('Unit')])]),
 *         new ListValue([new StringValue('tests')]),
 *     ))->rendered();
 */
final readonly class PhpunitTestsuites implements Rendered
{
    /**
     * Initializes with the testsuites tree and the base directories list.
     *
     * @param Value $suites TreeValue of suite name to subdir list; empty ListValue accepted for YAML `{}`
     * @param Value $roots ListValue of base directories (typically php.tests)
     */
    public function __construct(private Value $suites, private Value $roots) {}

    #[Override]
    public function rendered(): string
    {
        $bases = $this->stringList($this->roots, 'base directories');

        if ($bases === []) {
            throw new SheriffException('PhpunitTestsuites requires at least one base directory');
        }

        $entries = $this->entries();

        if ($entries === []) {
            return (new PhpunitSuiteBlock('default', $bases))->rendered();
        }

        $blocks = [];

        foreach ($entries as $name => $subdirs) {
            $subs = $this->stringList($subdirs, sprintf('suite "%s"', $name));
            $blocks[] = (new PhpunitSuiteBlock($name, $this->pairs($bases, $subs)))->rendered();
        }

        return implode("\n", $blocks);
    }

    /**
     * Builds the cartesian product of base/subdir pairs.
     *
     * @param list<string> $bases
     * @param list<string> $subs
     * @return list<string>
     */
    private function pairs(array $bases, array $subs): array
    {
        $paths = [];

        foreach ($bases as $base) {
            $paths = array_merge(
                $paths,
                array_map(static fn(string $sub): string => sprintf('%s/%s', $base, $sub), $subs),
            );
        }

        return $paths;
    }

    /**
     * Resolves the testsuites payload to its name-to-subdirs entries.
     *
     * @throws SheriffException
     * @return array<string, Value>
     */
    private function entries(): array
    {
        if ($this->suites instanceof TreeValue) {
            return $this->suites->entries;
        }

        if ($this->suites instanceof ListValue && $this->suites->children === []) {
            return [];
        }

        throw new SheriffException(
            sprintf('PhpunitTestsuites requires a TreeValue payload, got %s', get_debug_type($this->suites)),
        );
    }

    /**
     * Returns the list of strings under a ListValue payload.
     *
     * @throws SheriffException
     * @return list<string>
     */
    private function stringList(Value $value, string $label): array
    {
        if (!$value instanceof ListValue) {
            throw new SheriffException(
                sprintf('PhpunitTestsuites expects %s to be a list, got %s', $label, $value::class),
            );
        }

        $result = [];

        foreach ($value->children as $child) {
            if (!$child instanceof StringValue) {
                throw new SheriffException(
                    sprintf('PhpunitTestsuites expects %s to contain strings, got %s', $label, $child::class),
                );
            }

            $result[] = $child->raw;
        }

        return $result;
    }
}
