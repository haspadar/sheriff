<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\FastChecks;
use Haspadar\Sheriff\Tests\Fake\Check\FakeCheck;
use Haspadar\Sheriff\Tests\Fake\Check\FakeChecks;
use Haspadar\Sheriff\Tests\Fake\Config\FakeConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FastChecksTest extends TestCase
{
    #[Test]
    public function yieldsAllChecksWhenNoSlowConfigured(): void
    {
        $checks = new FastChecks(
            new FakeChecks([
                new FakeCheck('phpstan'),
                new FakeCheck('phpunit'),
            ]),
            new FakeConfig(['check.slow' => []]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpstan', 'phpunit'],
            $names,
            'FastChecks must yield all checks when check.slow is empty',
        );
    }

    #[Test]
    public function excludesSlowChecks(): void
    {
        $checks = new FastChecks(
            new FakeChecks([
                new FakeCheck('phpstan'),
                new FakeCheck('infection'),
                new FakeCheck('phpunit'),
            ]),
            new FakeConfig(['check.slow' => ['infection']]),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        self::assertSame(
            ['phpstan', 'phpunit'],
            $names,
            'FastChecks must exclude checks listed in check.slow config',
        );
    }

    #[Test]
    public function yieldsNothingWhenAllAreSlow(): void
    {
        $checks = new FastChecks(
            new FakeChecks([new FakeCheck('infection')]),
            new FakeConfig(['check.slow' => ['infection']]),
        );

        self::assertCount(
            0,
            iterator_to_array($checks->all()),
            'FastChecks must yield nothing when all checks are slow',
        );
    }
}
