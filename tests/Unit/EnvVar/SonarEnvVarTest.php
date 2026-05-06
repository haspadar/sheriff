<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\EnvVar;

use Haspadar\Sheriff\EnvVar\SonarEnvVar;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SonarEnvVarTest extends TestCase
{
    #[Test]
    public function disabledWhenCloudIsTrue(): void
    {
        self::assertFalse(
            (new SonarEnvVar())->enabled(new FakeSettings(['sonar.cloud' => new BoolValue(true)])),
            'SonarEnvVar must be disabled when sonar.cloud is true',
        );
    }

    #[Test]
    public function disabledWhenCloudIsAbsent(): void
    {
        self::assertFalse(
            (new SonarEnvVar())->enabled(new FakeSettings([])),
            'SonarEnvVar must default to cloud mode and be disabled when sonar.cloud is absent',
        );
    }

    #[Test]
    public function enabledWhenCloudFalseAndCliEnabled(): void
    {
        self::assertTrue(
            (new SonarEnvVar())->enabled(new FakeSettings([
                'sonar.cloud' => new BoolValue(false),
                'sonar.cli' => new BoolValue(true),
            ])),
            'SonarEnvVar must be enabled when sonar.cloud is false and sonar.cli is true',
        );
    }

    #[Test]
    public function disabledWhenCloudFalseAndCliDisabled(): void
    {
        self::assertFalse(
            (new SonarEnvVar())->enabled(new FakeSettings([
                'sonar.cloud' => new BoolValue(false),
                'sonar.cli' => new BoolValue(false),
            ])),
            'SonarEnvVar must be disabled when local scanner is turned off via sonar.cli',
        );
    }

    #[Test]
    public function returnsCorrectName(): void
    {
        self::assertSame(
            'SONAR_TOKEN',
            (new SonarEnvVar())->name(),
            'SonarEnvVar name must be SONAR_TOKEN',
        );
    }

    #[Test]
    public function returnsUrl(): void
    {
        self::assertSame(
            'https://sonarcloud.io/account/security',
            (new SonarEnvVar())->url(),
            'SonarEnvVar url must point to SonarCloud security page',
        );
    }
}
