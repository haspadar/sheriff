<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Output;

use Haspadar\Sheriff\Output\Message;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    #[Test]
    public function hasGivenBody(): void
    {
        self::assertSame(
            'hello',
            (new Message('hello'))->body(),
            'Message must return the body it was constructed with',
        );
    }
}
