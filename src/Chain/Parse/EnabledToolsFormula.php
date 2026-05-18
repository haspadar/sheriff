<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Chain\Plain\ListText;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Filters a tool-name list down to those whose `<name>.cli` flag is true.
 *
 * Reads a `ListValue` of `StringValue` tool names from settings, then keeps
 * only the names whose corresponding `<name>.cli` setting is `true`. Missing
 * `<name>.cli` declarations and non-boolean values fail fast at template render
 * time. The result is a `ListText` source op that downstream pipeline stages
 * (`EachFormatted`, `Joined`) consume as a plain list.
 *
 * Example:
 *
 *     (new EnabledToolsFormula(['ci.infra_checks']))
 *         ->op([], $settings); // ListText over the tools whose cli flag is true
 */
final readonly class EnabledToolsFormula implements Formula
{
    /**
     * Initializes with the raw template arguments — the single settings key.
     *
     * @param list<string> $args Raw template arguments; expects exactly one settings key referencing a ListValue of tool names
     */
    public function __construct(private array $args) {}

    #[Override]
    public function op(array $previous, Settings $settings): Op
    {
        if (count($this->args) !== 1) {
            throw new SheriffException(
                sprintf('EnabledTools expects exactly one settings key, got %d', count($this->args)),
            );
        }

        $key = $this->args[0];

        if (!$settings->has($key)) {
            throw new SheriffException(
                sprintf('EnabledTools cannot find settings key "%s"', $key),
            );
        }

        $list = $settings->value($key);

        if (!$list instanceof ListValue) {
            throw new SheriffException(
                sprintf('EnabledTools expects "%s" to be a list, got %s', $key, $list::class),
            );
        }

        $kept = [];

        foreach ($list->children as $child) {
            $kept = $this->keepWhenEnabled($child, $settings, $kept);
        }

        if ($kept === []) {
            throw new SheriffException(
                sprintf('EnabledTools resolved no enabled tools in "%s"', $key),
            );
        }

        return new ListText(new ListValue($kept));
    }

    /**
     * Appends the child to the accumulator when its `<name>.cli` flag is true.
     *
     * @param Value $child One element from the source ListValue, must be a StringValue
     * @param Settings $settings Settings context the formula reads from
     * @param list<StringValue> $kept Accumulator of already-kept names
     * @throws SheriffException
     * @return list<StringValue> Updated accumulator
     */
    private function keepWhenEnabled(Value $child, Settings $settings, array $kept): array
    {
        if (!$child instanceof StringValue) {
            throw new SheriffException(
                sprintf('EnabledTools expects string tool names, got %s', $child::class),
            );
        }

        $flag = sprintf('%s.cli', $child->raw);

        if (!$settings->has($flag)) {
            throw new SheriffException(
                sprintf('EnabledTools: setting "%s" not declared', $flag),
            );
        }

        $value = $settings->value($flag);

        if (!$value instanceof BoolValue) {
            throw new SheriffException(
                sprintf('EnabledTools expects "%s" to be a boolean, got %s', $flag, $value::class),
            );
        }

        if ($value->raw) {
            $kept[] = $child;
        }

        return $kept;
    }
}
