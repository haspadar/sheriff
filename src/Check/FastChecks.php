<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Override;

/**
 * Excludes slow checks listed in the "check.slow" settings key.
 */
final readonly class FastChecks implements Checks
{
    /**
     * Initializes with a check collection and project settings.
     *
     * @param Checks $origin Underlying collection to filter
     * @param Settings $settings Settings holding the "check.slow" key
     */
    public function __construct(private Checks $origin, private Settings $settings) {}

    #[Override]
    public function all(): iterable
    {
        $slow = $this->slow();

        foreach ($this->origin->all() as $check) {
            if (!in_array($check->name(), $slow, true)) {
                yield $check;
            }
        }
    }

    /**
     * Returns the names of slow checks declared in the settings.
     *
     * @return list<string>
     */
    private function slow(): array
    {
        if (!$this->settings->has('check.slow')) {
            return [];
        }

        $value = $this->settings->value('check.slow');

        if (!$value instanceof ListValue) {
            return [];
        }

        $names = [];

        foreach ($value->children as $child) {
            if ($child instanceof StringValue) {
                $names[] = $child->raw;
            }
        }

        return $names;
    }
}
