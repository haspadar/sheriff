<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Args;

use Haspadar\Sheriff\Formula\Args\ListArgs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListArgsTest extends TestCase
{
    #[Test]
    public function returnsValuesAsList(): void
    {
        self::assertSame(
            ['alpha', 'beta', 'gamma'],
            (new ListArgs(['alpha', 'beta', 'gamma']))->values(),
            'ListArgs must return all provided values',
        );
    }

    #[Test]
    public function returnsEmptyListWhenConstructedWithEmptyArray(): void
    {
        self::assertSame(
            [],
            (new ListArgs([]))->values(),
            'ListArgs must return an empty list when constructed with an empty array',
        );
    }
}
