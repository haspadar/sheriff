<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Formula;

use Haspadar\Sheriff\Formula\Action\Action;
use Haspadar\Sheriff\Formula\Actions\Actions;
use Override;

final readonly class FakeActions implements Actions
{
    /**
     * @param list<Action> $actions
     */
    public function __construct(
        private array $actions,
    ) {}

    /**
     * @return list<Action>
     */
    #[Override]
    public function all(): array
    {
        return $this->actions;
    }
}
