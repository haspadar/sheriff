<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Args;

use Override;

/**
 * Args backed by a plain PHP list of scalar values.
 */
final readonly class ListArgs implements Args
{
    /**
     * Initializes with a plain list of scalar values.
     *
     * @param list<int|float|string|bool> $values Scalar values exposed as formula arguments
     */
    public function __construct(private array $values) {}

    #[Override]
    public function values(): array
    {
        return $this->values;
    }
}
