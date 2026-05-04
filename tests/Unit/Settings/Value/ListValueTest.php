<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListValueTest extends TestCase
{
    #[Test]
    public function exposesNestedChildren(): void
    {
        $children = [new StringValue('src'), new StringValue('tests')];

        self::assertSame(
            $children,
            (new ListValue($children))->children,
            'ListValue must expose its nested values through the children property',
        );
    }
}
