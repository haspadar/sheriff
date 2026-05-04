<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\SingleCheck;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SingleCheckTest extends TestCase
{
    #[Test]
    public function yieldsSingleCheckWithGivenName(): void
    {
        $checks = new SingleCheck('phpunit', '/project');
        $items = iterator_to_array($checks->all());

        self::assertCount(
            1,
            $items,
            'SingleCheck must yield exactly one check',
        );

        self::assertSame(
            'phpunit',
            $items[0]->name(),
            'SingleCheck must yield a check with the given name',
        );
    }
}
