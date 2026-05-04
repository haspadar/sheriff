<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ToggleOption;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ToggleOptionTest extends TestCase
{
    #[Test]
    public function enabledWhenShortFlagPresent(): void
    {
        self::assertTrue(
            (new ToggleOption(['check', '-v'], ['-v', '--verbose']))->enabled(),
            'ToggleOption must be enabled when short flag is in argv',
        );
    }

    #[Test]
    public function enabledWhenLongFlagPresent(): void
    {
        self::assertTrue(
            (new ToggleOption(['check', '--verbose'], ['-v', '--verbose']))->enabled(),
            'ToggleOption must be enabled when long flag is in argv',
        );
    }

    #[Test]
    public function disabledWhenFlagAbsent(): void
    {
        self::assertFalse(
            (new ToggleOption(['check', 'phpstan'], ['-v', '--verbose']))->enabled(),
            'ToggleOption must be disabled when flag is not in argv',
        );
    }

    #[Test]
    public function disabledWhenArgvEmpty(): void
    {
        self::assertFalse(
            (new ToggleOption([], ['-p', '--parallel']))->enabled(),
            'ToggleOption must be disabled when argv is empty',
        );
    }

    #[Test]
    public function disabledWhenFlagHasValueSuffix(): void
    {
        self::assertFalse(
            (new ToggleOption(['check', '--verbose=1'], ['-v', '--verbose']))->enabled(),
            'ToggleOption must not match --verbose=1 as --verbose',
        );
    }
}
