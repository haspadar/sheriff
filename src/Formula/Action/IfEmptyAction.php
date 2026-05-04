<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Override;

/**
 * Passes empty input through, clears non-empty input.
 */
final readonly class IfEmptyAction implements Action
{
    #[Override]
    public function transformed(Args $args): Args
    {
        $values = $args->values();

        if ($values === [] || $values === ['']) {
            return $args;
        }

        return new ListArgs([]);
    }
}
