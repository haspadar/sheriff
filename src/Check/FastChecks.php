<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Config\Config;
use Override;

/**
 * Excludes slow checks listed in the "check.slow" config key.
 */
final readonly class FastChecks implements Checks
{
    /**
     * Initializes with a check collection and project configuration.
     *
     * @param Checks $origin Underlying collection to filter
     * @param Config $config Configuration holding the "check.slow" key
     */
    public function __construct(private Checks $origin, private Config $config) {}

    #[Override]
    public function all(): iterable
    {
        $slow = array_map('strval', $this->config->list('check.slow'));

        foreach ($this->origin->all() as $check) {
            if (!in_array($check->name(), $slow, true)) {
                yield $check;
            }
        }
    }
}
