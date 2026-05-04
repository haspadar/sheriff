<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Config\Config;
use Override;

/**
 * A CLI option whose value comes from a boolean config key.
 */
final readonly class ConfigDefault implements CliOption
{
    /**
     * Initializes with project configuration and the config key.
     *
     * @param Config $config Configuration to read the boolean value from
     * @param string $key Dot-separated config key holding the boolean default
     */
    public function __construct(private Config $config, private string $key) {}

    #[Override]
    public function enabled(): bool
    {
        return filter_var(
            $this->config->list($this->key)[0] ?? false,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? false;
    }
}
