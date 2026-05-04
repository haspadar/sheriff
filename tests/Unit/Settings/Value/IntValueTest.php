<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\IntValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IntValueTest extends TestCase
{
    #[Test]
    public function exposesIntegerPayload(): void
    {
        self::assertSame(
            8,
            (new IntValue(8))->raw,
            'IntValue must expose its integer payload through the raw property',
        );
    }
}
