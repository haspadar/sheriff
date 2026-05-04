<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Check;

use Haspadar\Sheriff\Check\Check;
use Override;

final readonly class FakeCheck implements Check
{
    public function __construct(private string $name, private string $command = '/usr/bin/true') {}

    #[Override]
    public function name(): string
    {
        return $this->name;
    }

    #[Override]
    public function command(): string
    {
        return $this->command;
    }
}
