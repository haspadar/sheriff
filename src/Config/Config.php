<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config;

use Haspadar\Sheriff\SheriffException;

/**
 * Read-only access to flat dot-notated configuration keys
 */
interface Config
{
    /**
     * Returns true if the key is declared in this configuration
     *
     * @throws SheriffException
     */
    public function has(string $name): bool;

    /**
     * Returns configuration values for a dot-notated path
     *
     * Missing paths and explicitly empty lists are both represented as an empty list
     *
     * @throws SheriffException
     * @return list<scalar>
     */
    public function list(string $name): array;

    /**
     * @throws SheriffException
     * @return array<string, scalar|list<scalar>>
     */
    public function toArray(): array;
}
