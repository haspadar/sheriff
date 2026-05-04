<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Secret;

use Haspadar\Sheriff\Secret\CodecovSecret;
use Haspadar\Sheriff\Secret\InfectionSecret;
use Haspadar\Sheriff\Secret\Secrets;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SecretsTest extends TestCase
{
    #[Test]
    public function returnsAllItems(): void
    {
        $codecov = new CodecovSecret();
        $infection = new InfectionSecret();

        self::assertSame(
            [$codecov, $infection],
            (new Secrets([$codecov, $infection]))->items(),
            'Secrets must return all items in order',
        );
    }

    #[Test]
    public function returnsEmptyListWhenNoItems(): void
    {
        self::assertSame(
            [],
            (new Secrets([]))->items(),
            'Secrets must return empty list when constructed with no items',
        );
    }
}
