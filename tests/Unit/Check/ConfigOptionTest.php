<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ConfigOption;
use Haspadar\Sheriff\Tests\Fake\Check\FakeCliOption;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigOptionTest extends TestCase
{
    #[Test]
    public function returnsFalseWhenAllFlagsDisabled(): void
    {
        self::assertFalse(
            (new ConfigOption(
                new FakeCliOption(false),
                new FakeCliOption(false),
                new FakeCliOption(false),
            ))->enabled(),
            'ConfigOption must be disabled when all flags are off',
        );
    }

    #[Test]
    public function returnsTrueWhenPositiveFlagEnabled(): void
    {
        self::assertTrue(
            (new ConfigOption(
                new FakeCliOption(true),
                new FakeCliOption(false),
                new FakeCliOption(false),
            ))->enabled(),
            'ConfigOption must be enabled when positive flag is set',
        );
    }

    #[Test]
    public function returnsFalseWhenNegativeFlagEnabled(): void
    {
        self::assertFalse(
            (new ConfigOption(
                new FakeCliOption(true),
                new FakeCliOption(true),
                new FakeCliOption(false),
            ))->enabled(),
            'ConfigOption must be disabled when negative flag overrides',
        );
    }

    #[Test]
    public function fallsBackToDefaultWhenNoFlagSet(): void
    {
        self::assertTrue(
            (new ConfigOption(
                new FakeCliOption(false),
                new FakeCliOption(false),
                new FakeCliOption(true),
            ))->enabled(),
            'ConfigOption must fall back to default when no flag is set',
        );
    }
}
