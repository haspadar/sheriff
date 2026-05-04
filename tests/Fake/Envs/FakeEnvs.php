<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Envs;

use Haspadar\Sheriff\Envs\Envs;
use Override;

final readonly class FakeEnvs implements Envs
{
    /** @param array<string, string> $vars */
    public function __construct(private array $vars) {}

    #[Override]
    public function vars(): array
    {
        return $this->vars;
    }
}
