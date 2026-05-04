<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Args;

use Override;

/**
 * Removes matching outer single or double quotes.
 *
 * Escape sequences inside the string are not processed
 */
final readonly class UnquotedArgs implements Args
{
    private const int MIN_QUOTED_LENGTH = 2;

    private const int STRIP_LAST_CHAR = -1;

    /**
     * Initializes with the args to unquote.
     *
     * @param Args $origin Args whose string values may carry matching outer quotes
     */
    public function __construct(private Args $origin) {}

    #[Override]
    public function values(): array
    {
        return array_map(
            fn(int|float|string|bool $value) => is_string($value)
                ? $this->unquote($value)
                : $value,
            $this->origin->values(),
        );
    }

    private function unquote(string $text): string
    {
        $length = strlen($text);

        if ($length < self::MIN_QUOTED_LENGTH) {
            return $text;
        }

        $first = $text[0];
        $last = $text[$length - 1];

        if (
            ($first === '"' && $last === '"')
            || ($first === "'" && $last === "'")
        ) {
            return substr($text, 1, self::STRIP_LAST_CHAR);
        }

        return $text;
    }
}
