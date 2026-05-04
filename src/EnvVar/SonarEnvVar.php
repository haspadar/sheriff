<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\EnvVar;

use Haspadar\Sheriff\Config\Config;
use Haspadar\Sheriff\SheriffException;
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
    public function enabled(Config $config): bool
    {
        if ($this->cloud($config)) {
            return false;
        }

        if (!$config->has('sonar.cli')) {
            return true;
        }

        return filter_var(
            $config->list('sonar.cli')[0] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? true;
    }

    /**
     * Checks whether SonarCloud mode is active.
     *
     * @throws SheriffException
     */
    private function cloud(Config $config): bool
    {
        if (!$config->has('sonar.cloud')) {
            return true;
        }

        return filter_var(
            $config->list('sonar.cloud')[0] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? true;
    }
}
