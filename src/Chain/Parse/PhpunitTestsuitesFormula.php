<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Chain\Render\Xml\PhpunitTestsuites;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Builds the PhpunitTestsuites source op from two settings keys.
 *
 * Accepts the testsuites tree key first and the base directories list key
 * second. Both keys are resolved against settings and the resolved Values
 * are passed to the source op as-is.
 *
 * Example:
 *
 *     (new PhpunitTestsuitesFormula(['phpunit.testsuites', 'php.tests']))
 *         ->op([], $settings);
 */
final readonly class PhpunitTestsuitesFormula implements Formula
{
    private const int EXPECTED_ARGS = 2;

    /**
     * Initializes with the raw template arguments — two settings keys.
     *
     * @param list<string> $args Raw template arguments; expects the testsuites tree key and the base directories list key
     */
    public function __construct(private array $args) {}

    #[Override]
    public function op(array $previous, Settings $settings): Op
    {
        if (count($this->args) !== self::EXPECTED_ARGS) {
            throw new SheriffException(
                sprintf(
                    'PhpunitTestsuites expects the testsuites key and the base directories key, got %d arguments',
                    count($this->args),
                ),
            );
        }

        $suites = $this->valueAt(0, $settings);
        $baseDirs = $this->valueAt(1, $settings);

        return new PhpunitTestsuites($suites, $baseDirs);
    }

    /**
     * Reads the value bound to the argument at the given index.
     *
     * @throws SheriffException
     */
    private function valueAt(int $index, Settings $settings): Value
    {
        $key = $this->args[$index];

        if (!$settings->has($key)) {
            throw new SheriffException(
                sprintf('PhpunitTestsuites cannot find settings key "%s"', $key),
            );
        }

        return $settings->value($key);
    }
}
