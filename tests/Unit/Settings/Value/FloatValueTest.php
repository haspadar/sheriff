<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\FloatValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FloatValueTest extends TestCase
{
    #[Test]
    public function exposesFloatPayload(): void
    {
        self::assertSame(
            0.5,
            (new FloatValue(0.5))->raw,
            'FloatValue must expose its floating-point payload through the raw property',
        );
    }
}
