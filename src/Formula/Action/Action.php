<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\SheriffException;
use InvalidArgumentException;

/**
 * A single step in the DSL action pipeline that transforms Args into Args
 */
interface Action
{
    /**
     * Applies this action to the given arguments and returns the result
     *
     * @throws InvalidArgumentException|SheriffException
     */
    public function transformed(Args $args): Args;
}
