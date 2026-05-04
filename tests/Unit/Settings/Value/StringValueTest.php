<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StringValueTest extends TestCase
{
    #[Test]
    public function exposesStringPayload(): void
    {
        self::assertSame(
            '1G',
            (new StringValue('1G'))->raw,
            'StringValue must expose its string payload through the raw property',
        );
    }
}
