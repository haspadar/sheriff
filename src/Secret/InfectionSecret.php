<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Secret;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\BoolValue;
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
    public function enabled(Settings $settings): bool
    {
        if (!$settings->has('infection.cli')) {
            return true;
        }

        $value = $settings->value('infection.cli');

        return $value instanceof BoolValue
            ? $value->raw
            : true;
    }
}
