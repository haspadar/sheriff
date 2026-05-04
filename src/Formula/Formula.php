<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula;

use Haspadar\Sheriff\SheriffException;
use InvalidArgumentException;

/**
 * A DSL expression that resolves to a scalar string
 */
interface Formula
{
    /**
     * Evaluates the expression and returns the resulting string
     *
     * @throws InvalidArgumentException|SheriffException
     */
    public function result(): string;
}
