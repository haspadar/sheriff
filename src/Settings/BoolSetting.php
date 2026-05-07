<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings;

use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\SheriffException;

/**
 * Reads a boolean setting from Settings with a fallback default.
 *
 * Returns the default when the key is absent or the stored Value is not a
 * BoolValue. Centralises the strict bool lookup that EnvVar/Secret toggles
 * share so the contract stays consistent.
 *
 * Example:
 *
 *     (new BoolSetting($settings, 'sonar.cloud', true))->raw();
 */
final readonly class BoolSetting
{
    /**
     * Initializes with the source settings, the key, and the default fallback.
     *
     * @param Settings $settings Settings to query
     * @param string $key Configuration key whose boolean value is read
     * @param bool $default Value returned when the key is absent or not boolean
     */
    public function __construct(
        private Settings $settings,
        private string $key,
        private bool $default,
    ) {}

    /**
     * Returns the boolean payload, falling back to the default.
     *
     * @throws SheriffException
     */
    public function raw(): bool
    {
        if (!$this->settings->has($this->key)) {
            return $this->default;
        }

        $value = $this->settings->value($this->key);

        return $value instanceof BoolValue
            ? $value->raw
            : $this->default;
    }
}
