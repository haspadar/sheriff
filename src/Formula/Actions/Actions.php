<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Actions;

use Haspadar\Sheriff\Formula\Action\Action;
use Haspadar\Sheriff\SheriffException;

/**
 * An ordered sequence of DSL pipeline actions
 */
interface Actions
{
    /**
     * Returns the ordered list of actions.
     *
     * @throws SheriffException
     * @return list<Action>
     */
    public function all(): array;
}
