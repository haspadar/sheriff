<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Parse;

use Haspadar\Sheriff\SheriffException;

/**
 * Argument list from one pipeline formula call.
 *
 * Example:
 *
 *     (new FormulaArgs('phpstan.paths, "- %s"'))->values();
 */
final readonly class FormulaArgs
{
    private const int MIN_QUOTED_LENGTH = 2;

    private const int STRIP_LAST_CHAR = -1;

    /**
     * Initializes with the raw text inside formula parentheses.
     *
     * @param string $text Raw argument text
     */
    public function __construct(private string $text) {}

    /**
     * Returns unquoted argument values.
     *
     * @throws SheriffException
     * @return list<string>
     */
    public function values(): array
    {
        if (trim($this->text) === '') {
            return [];
        }

        return array_map(
            $this->unquoted(...),
            (new SeparatedTexts($this->text, ','))->values(),
        );
    }

    private function unquoted(string $argument): string
    {
        $length = strlen($argument);

        if ($length < self::MIN_QUOTED_LENGTH) {
            return $argument;
        }

        $first = $argument[0];
        $last = $argument[$length - 1];

        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            return $this->unescaped(substr($argument, 1, self::STRIP_LAST_CHAR));
        }

        return $argument;
    }

    private function unescaped(string $argument): string
    {
        $result = '';
        $escaped = false;

        for ($offset = 0; $offset < strlen($argument); ++$offset) {
            $char = $argument[$offset];

            if ($escaped) {
                $result .= $this->escaped($char);
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            $result .= $char;
        }

        return $escaped ? sprintf('%s\\', $result) : $result;
    }

    private function escaped(string $char): string
    {
        return match ($char) {
            'n' => "\n",
            'r' => "\r",
            't' => "\t",
            '\\' => '\\',
            '"' => '"',
            "'" => "'",
            default => sprintf('\\%s', $char),
        };
    }
}
