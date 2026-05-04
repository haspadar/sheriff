<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ConfigDefault;
use Haspadar\Sheriff\Tests\Fake\Config\FakeConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigDefaultTest extends TestCase
{
    #[Test]
    public function enabledWhenConfigValueIsTrue(): void
    {
        self::assertTrue(
            (new ConfigDefault(
                new FakeConfig(['check.verbose' => ['true']]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be enabled when config value is "true"',
        );
    }

    #[Test]
    public function enabledWhenConfigValueIsOne(): void
    {
        self::assertTrue(
            (new ConfigDefault(
                new FakeConfig(['check.verbose' => ['1']]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be enabled when config value is "1"',
        );
    }

    #[Test]
    public function disabledWhenConfigValueIsFalse(): void
    {
        self::assertFalse(
            (new ConfigDefault(
                new FakeConfig(['check.verbose' => ['false']]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be disabled when config value is "false"',
        );
    }

    #[Test]
    public function disabledWhenConfigValueIsZero(): void
    {
        self::assertFalse(
            (new ConfigDefault(
                new FakeConfig(['check.verbose' => ['0']]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be disabled when config value is "0"',
        );
    }

    #[Test]
    public function disabledWhenConfigKeyAbsent(): void
    {
        self::assertFalse(
            (new ConfigDefault(
                new FakeConfig([]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be disabled when config key is absent',
        );
    }

    #[Test]
    public function disabledWhenConfigValueIsUnparseable(): void
    {
        self::assertFalse(
            (new ConfigDefault(
                new FakeConfig(['check.verbose' => ['maybe']]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be disabled when config value cannot be parsed as boolean',
        );
    }
}
