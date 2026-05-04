<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Args;

use InvalidArgumentException;

/**
 * Represents a sequence of runtime values flowing through a DSL action pipeline
 *
 * Args is a value container
 * It does not perform formatting, parsing, or semantic interpretation
 * Each action transforms one Args instance into another
 */
interface Args
{
    /**
     * Returns ordered values produced by the previous action
     *
     * @throws InvalidArgumentException
     * @return list<int|float|string|bool>
     */
    public function values(): array;
}
