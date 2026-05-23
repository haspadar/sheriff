<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\BoolValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BoolValueTest extends TestCase
{
    #[Test]
    public function exposesBooleanPayload(): void
    {
        self::assertTrue(
            (new BoolValue(true))->raw,
            'BoolValue must expose its boolean payload through the raw property',
        );
    }
}
