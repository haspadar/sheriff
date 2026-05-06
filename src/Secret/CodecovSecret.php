<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Secret;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\BoolValue;
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
    public function enabled(Settings $settings): bool
    {
        if (!$settings->has('phpunit.cli')) {
            return true;
        }

        $value = $settings->value('phpunit.cli');

        return $value instanceof BoolValue
            ? $value->raw
            : true;
    }
}
