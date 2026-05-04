<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Secret;

use Haspadar\Sheriff\Config\Config;
use Override;

/**
 * Codecov coverage upload token.
 */
final readonly class CodecovSecret implements Secret
{
    #[Override]
    public function name(): string
    {
        return 'CODECOV_TOKEN';
    }

    #[Override]
    public function url(string $org): string
    {
        return "https://app.codecov.io/account/gh/{$org}/repositories";
    }

    #[Override]
    public function enabled(Config $config): bool
    {
        if (!$config->has('phpunit.cli')) {
            return true;
        }

        return filter_var(
            $config->list('phpunit.cli')[0] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? true;
    }
}
