<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Check;

use Haspadar\Sheriff\Check\Check;
use Haspadar\Sheriff\Check\Checks;
use Override;

final readonly class FakeChecks implements Checks
{
    /** @param list<Check> $checks */
    public function __construct(private array $checks) {}

    #[Override]
    public function all(): iterable
    {
        return $this->checks;
    }
}
