<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Args;

use Override;

/**
 * Trims leading and trailing whitespace from string values; non-strings are passed through unchanged.
 */
final readonly class TrimmedArgs implements Args
{
    /**
     * Initializes with the args to trim.
     *
     * @param Args $origin Args whose string values will be whitespace-trimmed
     */
    public function __construct(private Args $origin) {}

    #[Override]
    public function values(): array
    {
        return array_map(
            static function (int|float|string|bool $value): int|float|string|bool {
                if (!is_string($value)) {
                    return $value;
                }

                return trim($value);
            },
            $this->origin->values(),
        );
    }
}
