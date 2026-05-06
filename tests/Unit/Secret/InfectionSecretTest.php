<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Secret;

use Haspadar\Sheriff\Secret\InfectionSecret;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InfectionSecretTest extends TestCase
{
    #[Test]
    public function enabledWhenInfectionEnabled(): void
    {
        self::assertTrue(
            (new InfectionSecret())->enabled(new FakeSettings(['infection.cli' => new BoolValue(true)])),
            'InfectionSecret must be enabled when infection.cli is true',
        );
    }

    #[Test]
    public function enabledWhenKeyAbsent(): void
    {
        self::assertTrue(
            (new InfectionSecret())->enabled(new FakeSettings([])),
            'InfectionSecret must default to enabled when infection.cli key is absent',
        );
    }

    #[Test]
    public function disabledWhenInfectionDisabled(): void
    {
        self::assertFalse(
            (new InfectionSecret())->enabled(new FakeSettings(['infection.cli' => new BoolValue(false)])),
            'InfectionSecret must be disabled when infection.cli is false',
        );
    }

    #[Test]
    public function returnsCorrectName(): void
    {
        self::assertSame(
            'STRYKER_DASHBOARD_API_KEY',
            (new InfectionSecret())->name(),
            'InfectionSecret name must be STRYKER_DASHBOARD_API_KEY',
        );
    }

    #[Test]
    public function returnsUrl(): void
    {
        self::assertSame(
            'https://dashboard.stryker-mutator.io',
            (new InfectionSecret())->url('acme'),
            'InfectionSecret url must point to Stryker dashboard',
        );
    }
}
