<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\StringifiedArgs;
use Haspadar\Sheriff\Formula\Args\UnquotedArgs;
use Haspadar\Sheriff\SheriffException;
use InvalidArgumentException;
use Override;

/**
 * Replaces every occurrence of a substring in each incoming value.
 */
final readonly class ReplaceAction implements Action
{
    private const array ESCAPE_REPLACEMENTS = [
        '\\\\' => '\\',
        '\\n' => "\n",
        '\\r' => "\r",
        '\\t' => "\t",
    ];

    private const int PAIR_COUNT = 2;

    /**
     * Initializes with the raw "search, replace" argument string.
     *
     * @param string $raw Raw two-argument string in the form "search, replace"
     */
    public function __construct(private string $raw) {}

    #[Override]
    public function transformed(Args $args): Args
    {
        [$search, $replace] = $this->pair();

        return new ListArgs(
            array_map(
                static fn(int|float|string|bool $item): string => str_replace(
                    $search,
                    $replace,
                    (string) $item,
                ),
                (new StringifiedArgs($args))->values(),
            ),
        );
    }

    /**
     * Splits the raw argument string into (search, replace) with escape sequences resolved.
     *
     * @throws InvalidArgumentException|SheriffException
     * @return array{string, string}
     */
    private function pair(): array
    {
        $parts = array_map('trim', explode(',', $this->raw, self::PAIR_COUNT));

        if (count($parts) !== self::PAIR_COUNT) {
            throw new SheriffException(
                'Action "replace" requires two arguments: search and replace',
            );
        }

        $values = (new UnquotedArgs(new ListArgs($parts)))->values();

        return [
            $this->normalize((string) $values[0]),
            $this->normalize((string) $values[1]),
        ];
    }

    private function normalize(string $value): string
    {
        return strtr($value, self::ESCAPE_REPLACEMENTS);
    }
}
