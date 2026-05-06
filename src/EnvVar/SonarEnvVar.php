<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\EnvVar;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Override;

/**
 * SonarCloud scanner token for local analysis.
 */
final readonly class SonarEnvVar implements EnvVar
{
    #[Override]
    public function name(): string
    {
        return 'SONAR_TOKEN';
    }

    #[Override]
    public function url(): string
    {
        return 'https://sonarcloud.io/account/security';
    }

    #[Override]
    public function enabled(Settings $settings): bool
    {
        if ($this->boolean($settings, 'sonar.cloud', true)) {
            return false;
        }

        return $this->boolean($settings, 'sonar.cli', true);
    }

    private function boolean(Settings $settings, string $key, bool $default): bool
    {
        if (!$settings->has($key)) {
            return $default;
        }

        $value = $settings->value($key);

        return $value instanceof BoolValue
            ? $value->raw
            : $default;
    }
}
