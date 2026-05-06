<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ConfigDefault;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigDefaultTest extends TestCase
{
    #[Test]
    public function enabledWhenSettingsValueIsTrue(): void
    {
        self::assertTrue(
            (new ConfigDefault(
                new FakeSettings(['check.verbose' => new BoolValue(true)]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be enabled when the boolean setting is true',
        );
    }

    #[Test]
    public function disabledWhenSettingsValueIsFalse(): void
    {
        self::assertFalse(
            (new ConfigDefault(
                new FakeSettings(['check.verbose' => new BoolValue(false)]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must be disabled when the boolean setting is false',
        );
    }

    #[Test]
    public function disabledWhenSettingsKeyAbsent(): void
    {
        self::assertFalse(
            (new ConfigDefault(
                new FakeSettings([]),
                'check.verbose',
            ))->enabled(),
            'ConfigDefault must default to disabled when the settings key is absent',
        );
    }
}
