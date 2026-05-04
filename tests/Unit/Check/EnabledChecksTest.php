<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\EnabledChecks;
use Haspadar\Sheriff\Tests\Fake\Check\FakeCheck;
use Haspadar\Sheriff\Tests\Fake\Check\FakeChecks;
use Haspadar\Sheriff\Tests\Fake\Config\FakeConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnabledChecksTest extends TestCase
{
    #[Test]
    public function yieldsAllChecksWhenNoneDisabled(): void
    {
        $checks = new EnabledChecks(
            new FakeChecks([
                new FakeCheck('phpstan'),
                new FakeCheck('phpunit'),
            ]),
            new FakeConfig([]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpstan', 'phpunit'],
            $names,
            'EnabledChecks must yield all checks when no cli config disables them',
        );
    }

    #[Test]
    public function skipsCheckWhenCliConfigIsFalse(): void
    {
        $checks = new EnabledChecks(
            new FakeChecks([
                new FakeCheck('phpstan'),
                new FakeCheck('phpunit'),
            ]),
            new FakeConfig(['phpstan.cli' => [false]]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpunit'],
            $names,
            'EnabledChecks must skip checks whose .cli config is false',
        );
    }

    #[Test]
    public function yieldsCheckWhenCliConfigIsTrue(): void
    {
        $checks = new EnabledChecks(
            new FakeChecks([new FakeCheck('phpstan')]),
            new FakeConfig(['phpstan.cli' => [true]]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpstan'],
            $names,
            'EnabledChecks must yield check when .cli config is true',
        );
    }
}
