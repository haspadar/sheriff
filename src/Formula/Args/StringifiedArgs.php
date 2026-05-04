<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Args;

use Override;

/**
 * Converts all scalar values to strings, mapping true/false to "true"/"false".
 */
final readonly class StringifiedArgs implements Args
{
    /**
     * Initializes with the args to stringify.
     *
     * @param Args $origin Args whose scalar values will be converted to strings
     */
    public function __construct(private Args $origin) {}

    #[Override]
    public function values(): array
    {
        return array_map(
            fn(int|float|string|bool $value): string => $this->stringify($value),
            $this->origin->values(),
        );
    }

    private function stringify(int|float|string|bool $value): string
    {
        return match ($value) {
            true => 'true',
            false => 'false',
            default => (string) $value,
        };
    }
}
