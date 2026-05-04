<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Secret;

/**
 * Collection of CI secrets.
 */
final readonly class Secrets
{
    /**
     * Initializes with a list of CI secrets.
     *
     * @param list<Secret> $items CI secrets included in this collection
     */
    public function __construct(private array $items) {}

    /**
     * Returns all secrets in this collection.
     *
     * @return list<Secret>
     */
    public function items(): array
    {
        return $this->items;
    }
}
