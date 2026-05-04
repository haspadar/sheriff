<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fake\Formula;

use Haspadar\Sheriff\Formula\Action\Action;
use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;

final readonly class FakeAction implements Action
{
    /**
     * @param list<int|float|string|bool> $result
     */
    public function __construct(private array $result) {}

    public function transformed(Args $args): Args
    {
        return new ListArgs($this->result);
    }
}
