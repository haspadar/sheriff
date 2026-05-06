<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\EnabledChecks;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Tests\Fake\Check\FakeCheck;
use Haspadar\Sheriff\Tests\Fake\Check\FakeChecks;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
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
            new FakeSettings([]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpstan', 'phpunit'],
            $names,
            'EnabledChecks must yield all checks when no cli toggle disables them',
        );
    }

    #[Test]
    public function skipsCheckWhenCliToggleIsFalse(): void
    {
        $checks = new EnabledChecks(
            new FakeChecks([
                new FakeCheck('phpstan'),
                new FakeCheck('phpunit'),
            ]),
            new FakeSettings(['phpstan.cli' => new BoolValue(false)]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpunit'],
            $names,
            'EnabledChecks must skip checks whose .cli toggle is false',
        );
    }

    #[Test]
    public function yieldsCheckWhenCliToggleIsTrue(): void
    {
        $checks = new EnabledChecks(
            new FakeChecks([new FakeCheck('phpstan')]),
            new FakeSettings(['phpstan.cli' => new BoolValue(true)]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpstan'],
            $names,
            'EnabledChecks must yield checks whose .cli toggle is true',
        );
    }
}
