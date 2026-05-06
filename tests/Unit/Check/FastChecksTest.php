<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\FastChecks;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Tests\Fake\Check\FakeCheck;
use Haspadar\Sheriff\Tests\Fake\Check\FakeChecks;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
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
            new FakeSettings(['check.slow' => new ListValue([])]),
        );

        self::assertSame(
            ['phpstan', 'phpunit'],
            array_map(
                static fn($c) => $c->name(),
                iterator_to_array($checks->all()),
            ),
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
            new FakeSettings([
                'check.slow' => new ListValue([new StringValue('infection')]),
            ]),
        );

        self::assertSame(
            ['phpstan', 'phpunit'],
            array_map(
                static fn($c) => $c->name(),
                iterator_to_array($checks->all()),
            ),
            'FastChecks must exclude checks listed in check.slow',
        );
    }

    #[Test]
    public function excludesEverySlowEntryFromMultiEntryList(): void
    {
        $checks = new FastChecks(
            new FakeChecks([
                new FakeCheck('phpstan'),
                new FakeCheck('infection'),
                new FakeCheck('sonar'),
                new FakeCheck('phpunit'),
            ]),
            new FakeSettings([
                'check.slow' => new ListValue([
                    new StringValue('infection'),
                    new StringValue('sonar'),
                ]),
            ]),
        );

        self::assertSame(
            ['phpstan', 'phpunit'],
            array_map(
                static fn($c) => $c->name(),
                iterator_to_array($checks->all()),
            ),
            'FastChecks must drop every entry listed in check.slow, not just the first',
        );
    }

    #[Test]
    public function yieldsNothingWhenAllAreSlow(): void
    {
        $checks = new FastChecks(
            new FakeChecks([new FakeCheck('infection')]),
            new FakeSettings([
                'check.slow' => new ListValue([new StringValue('infection')]),
            ]),
        );

        self::assertSame(
            [],
            iterator_to_array($checks->all()),
            'FastChecks must yield nothing when all checks are slow',
        );
    }
}
