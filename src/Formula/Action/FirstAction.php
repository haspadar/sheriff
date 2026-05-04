<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Override;

/**
 * Returns only the first value from the incoming list.
 */
final readonly class FirstAction implements Action
{
    #[Override]
    public function transformed(Args $args): Args
    {
        $values = $args->values();

        return new ListArgs($values === [] ? [''] : [$values[0]]);
    }
}
