<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Settings\BoolSetting;
use Haspadar\Sheriff\Settings\Settings;
use Override;

/**
 * Yields only checks that are not explicitly disabled via settings.
 */
final readonly class EnabledChecks implements Checks
{
    /**
     * Initializes with a check collection and project settings.
     *
     * @param Checks $origin Underlying collection to filter
     * @param Settings $settings Settings holding the "<tool>.cli" toggles
     */
    public function __construct(private Checks $origin, private Settings $settings) {}

    #[Override]
    public function all(): iterable
    {
        foreach ($this->origin->all() as $check) {
            if ((new BoolSetting($this->settings, "{$check->name()}.cli", true))->raw()) {
                yield $check;
            }
        }
    }
}
