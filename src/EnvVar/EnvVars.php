<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\EnvVar;

/**
 * Collection of local environment variables.
 */
final readonly class EnvVars
{
    /**
     * Initializes with a list of environment variables.
     *
     * @param list<EnvVar> $items Environment variable entries included in this collection
     */
    public function __construct(private array $items) {}

    /**
     * Returns all environment variables in this collection.
     *
     * @return list<EnvVar>
     */
    public function items(): array
    {
        return $this->items;
    }
}
