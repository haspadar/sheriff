<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Override;

/**
 * Passes non-empty input through, clears empty input.
 */
final readonly class IfNotEmptyAction implements Action
{
    #[Override]
    public function transformed(Args $args): Args
    {
        $values = $args->values();

        if ($values === [] || $values === ['']) {
            return new ListArgs([]);
        }

        return $args;
    }
}
