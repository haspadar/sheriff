<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\BoolSetting;
use Override;

/**
 * A CLI option whose value comes from a boolean settings key.
 */
final readonly class ConfigDefault implements CliOption
{
    /**
     * Initializes with project settings and the boolean key.
     *
     * @param Settings $settings Settings to read the boolean value from
     * @param string $key Dot-separated key holding the boolean default
     */
    public function __construct(private Settings $settings, private string $key) {}

    #[Override]
    public function enabled(): bool
    {
        return (new BoolSetting($this->settings, $this->key, false))->raw();
    }
}
