<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Secret;

use Haspadar\Sheriff\Config\Config;
use Override;

/**
 * Stryker mutation testing dashboard token.
 */
final readonly class InfectionSecret implements Secret
{
    #[Override]
    public function name(): string
    {
        return 'STRYKER_DASHBOARD_API_KEY';
    }

    #[Override]
    public function url(string $org): string
    {
        return 'https://dashboard.stryker-mutator.io';
    }

    #[Override]
    public function enabled(Config $config): bool
    {
        if (!$config->has('infection.cli')) {
            return true;
        }

        return filter_var(
            $config->list('infection.cli')[0] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? true;
    }
}
