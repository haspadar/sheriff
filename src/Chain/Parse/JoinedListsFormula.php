<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Chain\Plain\ListText;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Combines two list settings into the cartesian product joined by a separator.
 *
 * Reads two settings keys, each pointing to a ListValue of StringValues, and
 * produces one StringValue per pair (left, right) joined by the literal
 * separator. The order is left-major: every right is emitted for the first
 * left, then for the second, and so on. When either side resolves to an empty
 * list the result is empty as well; missing keys, non-list values and
 * non-string elements fail fast. The result is a `ListText` source op that
 * downstream pipeline stages consume as a plain list.
 *
 * Example:
 *
 *     (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
 *         ->op([], $settings);
 *     // ListText over ["tests/Unit"] when php.tests=["tests"], unit=["Unit"]
 */
final readonly class JoinedListsFormula implements Formula
{
    private const int EXPECTED_ARGS = 3;

    private const int SEPARATOR_INDEX = 2;

    /**
     * Initializes with the raw template arguments — two settings keys and a separator.
     *
     * @param list<string> $args Raw template arguments; expects two settings keys followed by the separator literal
     */
    public function __construct(private array $args) {}

    #[Override]
    public function op(array $previous, Settings $settings): Op
    {
        if (count($this->args) !== self::EXPECTED_ARGS) {
            throw new SheriffException(
                sprintf(
                    'JoinedLists expects two settings keys and a separator, got %d arguments',
                    count($this->args),
                ),
            );
        }

        $left = $this->stringList($this->args[0], $settings);
        $right = $this->stringList($this->args[1], $settings);
        $separator = $this->args[self::SEPARATOR_INDEX];

        return new ListText(new ListValue($this->pairs($left, $right, $separator)));
    }

    /**
     * Reads the named key as a list of StringValues.
     *
     * @throws SheriffException
     * @return list<StringValue>
     */
    private function stringList(string $key, Settings $settings): array
    {
        if (!$settings->has($key)) {
            throw new SheriffException(
                sprintf('JoinedLists cannot find settings key "%s"', $key),
            );
        }

        $value = $settings->value($key);

        if (!$value instanceof ListValue) {
            throw new SheriffException(
                sprintf('JoinedLists expects "%s" to be a list, got %s', $key, $value::class),
            );
        }

        $strings = [];

        foreach ($value->children as $child) {
            $strings[] = $this->asString($key, $child);
        }

        return $strings;
    }

    /**
     * Asserts the child is a StringValue, returning it unchanged.
     *
     * @throws SheriffException
     */
    private function asString(string $key, Value $child): StringValue
    {
        if (!$child instanceof StringValue) {
            throw new SheriffException(
                sprintf('JoinedLists expects "%s" to contain strings, got %s', $key, $child::class),
            );
        }

        return $child;
    }

    /**
     * Builds the left-major cartesian product joined by the separator.
     *
     * @param list<StringValue> $left
     * @param list<StringValue> $right
     * @return list<StringValue>
     */
    private function pairs(array $left, array $right, string $separator): array
    {
        $result = [];

        foreach ($left as $leftPart) {
            foreach ($right as $rightPart) {
                $result[] = new StringValue(sprintf('%s%s%s', $leftPart->raw, $separator, $rightPart->raw));
            }
        }

        return $result;
    }
}
