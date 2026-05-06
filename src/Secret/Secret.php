<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Secret;

use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\SheriffException;

/**
 * GitHub Secret required by a CI service.
 */
interface Secret
{
    public function name(): string;

    public function url(string $org): string;

    /** @throws SheriffException */
    public function enabled(Settings $settings): bool;
}
