<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use InvalidArgumentException;

/**
 * Text split by a separator that is ignored inside quoted fragments.
 *
 * Example:
 *
 *     (new SeparatedTexts('A("x|y")|B()', '|'))->values();
 */
final readonly class SeparatedTexts
{
    /**
     * Initializes with the raw text and a single-character separator.
     *
     * @param string $text Text to split
     * @param string $separator Separator character
     */
    public function __construct(private string $text, private string $separator) {}

    /**
     * Returns trimmed fragments while preserving quoted contents.
     *
     * @throws InvalidArgumentException
     * @return list<string>
     */
    public function values(): array
    {
        $values = preg_split($this->pattern(), $this->text);

        if (!is_array($values)) {
            throw new InvalidArgumentException(sprintf('Cannot split "%s"', $this->text));
        }

        return array_map(
            static fn(string $value): string => trim($value),
            $values,
        );
    }

    /**
     * Returns the regex used to find separators outside quoted text.
     *
     * @return non-empty-string
     */
    private function pattern(): string
    {
        return sprintf(
            '/%s(?=(?:[^\'"\\\\]|\\\\.|"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\')*$)/',
            preg_quote($this->separator, '/'),
        );
    }
}
