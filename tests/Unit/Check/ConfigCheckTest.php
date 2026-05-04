<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ConfigCheck;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigCheckTest extends TestCase
{
    #[Test]
    public function returnsNamePassedToConstructor(): void
    {
        self::assertSame(
            'phpstan',
            (new ConfigCheck('phpstan', '/tmp'))->name(),
            'ConfigCheck must return the check name',
        );
    }

    #[Test]
    public function returnsCommandPathUnderSheriffDirectory(): void
    {
        self::assertSame(
            '/project/.sheriff/phpstan/command.sh',
            (new ConfigCheck('phpstan', '/project'))->command(),
            'ConfigCheck must build command path from root and name',
        );
    }
}
