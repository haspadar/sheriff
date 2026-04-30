<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Formula\Action;

use Haspadar\Piqule\Formula\Args\Args;
use Haspadar\Piqule\PiquleException;
use InvalidArgumentException;

/**
 * A single step in the DSL action pipeline that transforms Args into Args
 */
interface Action
{
    /**
     * Applies this action to the given arguments and returns the result
     *
     * @throws InvalidArgumentException|PiquleException
     */
    public function transformed(Args $args): Args;
}
