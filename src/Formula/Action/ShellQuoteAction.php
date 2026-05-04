<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\StringifiedArgs;
use Override;

/**
 * Wraps each value in POSIX single-quoted form for safe shell interpolation.
 */
final readonly class ShellQuoteAction implements Action
{
    #[Override]
    public function transformed(Args $args): Args
    {
        return new ListArgs(
            array_map(
                static fn(int|float|string|bool $item): string => sprintf(
                    "'%s'",
                    str_replace("'", "'\\''", (string) $item),
                ),
                (new StringifiedArgs($args))->values(),
            ),
        );
    }
}
