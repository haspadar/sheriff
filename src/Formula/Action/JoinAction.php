<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\UnquotedArgs;
use Override;

/**
 * Joins all incoming values into a single string using a delimiter.
 */
final readonly class JoinAction implements Action
{
    /**
     * Initializes with a raw delimiter string.
     *
     * @param string $raw Raw delimiter string, including any quoting and escape sequences
     */
    public function __construct(private string $raw) {}

    #[Override]
    public function transformed(Args $args): Args
    {
        $values = (new UnquotedArgs(
            new ListArgs([$this->raw]),
        ))->values();

        $delimiter = $this->normalize((string) ($values[0] ?? ''));

        $items = $args->values();

        if ($items === []) {
            return new ListArgs(['']);
        }

        return new ListArgs([
            implode($delimiter, $items),
        ]);
    }

    private function normalize(string $value): string
    {
        return strtr(
            $value,
            [
                '\\\\' => '\\',
                '\\n' => "\n",
                '\\r' => "\r",
                '\\t' => "\t",
            ],
        );
    }
}
