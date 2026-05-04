<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Config\Config;
use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\StringifiedArgs;
use Override;

/**
 * Loads values from configuration by key, ignoring any incoming args.
 */
final readonly class ConfigAction implements Action
{
    /**
     * Initializes with a configuration source and a key to look up.
     *
     * @param Config $config Configuration to read the value from
     * @param string $key Dot-separated key identifying the configuration entry
     */
    public function __construct(private Config $config, private string $key) {}

    #[Override]
    public function transformed(Args $args): Args
    {
        return new StringifiedArgs(
            new ListArgs($this->config->list($this->key)),
        );
    }
}
