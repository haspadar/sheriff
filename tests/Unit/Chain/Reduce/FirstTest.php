<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Reduce;

use Haspadar\Sheriff\Chain\Plain\StringText;
use Haspadar\Sheriff\Chain\Reduce\First;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FirstTest extends TestCase
{
    #[Test]
    public function rendersOnlyTheFirstPart(): void
    {
        self::assertSame(
            '8.3',
            (new First([
                new StringText(new StringValue('8.3')),
                new StringText(new StringValue('8.4')),
            ]))->rendered(),
            'First must render only the first part of the list',
        );
    }

    #[Test]
    public function failsWhenListIsEmpty(): void
    {
        $this->expectException(SheriffException::class);

        (new First([]))->rendered();
    }
}
