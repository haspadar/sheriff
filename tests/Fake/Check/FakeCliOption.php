<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Check;

use Haspadar\Sheriff\Check\CliOption;

final readonly class FakeCliOption implements CliOption
{
    public function __construct(private bool $enabled) {}

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
