<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Formula;

use Haspadar\Piqule\PiquleException;
use InvalidArgumentException;

/**
 * A DSL expression that resolves to a scalar string
 */
interface Formula
{
    /**
     * Evaluates the expression and returns the resulting string
     *
     * @throws InvalidArgumentException|PiquleException
     */
    public function result(): string;
}
