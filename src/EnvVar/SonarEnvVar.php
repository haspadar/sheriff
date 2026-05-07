<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\EnvVar;

use Haspadar\Sheriff\Settings\BoolSetting;
use Haspadar\Sheriff\Settings\Settings;
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
        if ((new BoolSetting($settings, 'sonar.cloud', true))->raw()) {
            return false;
        }

        return (new BoolSetting($settings, 'sonar.cli', true))->raw();
    }
}
