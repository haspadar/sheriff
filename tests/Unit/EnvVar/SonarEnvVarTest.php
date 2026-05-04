<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\EnvVar;

use Haspadar\Sheriff\EnvVar\SonarEnvVar;
use Haspadar\Sheriff\Tests\Fake\Config\FakeConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SonarEnvVarTest extends TestCase
{
    #[Test]
    public function disabledWhenCloudTrue(): void
    {
        self::assertSame(
            false,
            (new SonarEnvVar())->enabled(new FakeConfig(['sonar.cloud' => [true]])),
            'SonarEnvVar must be disabled when sonar.cloud is true',
        );
    }

    #[Test]
    public function disabledWhenCloudAbsent(): void
    {
        self::assertSame(
            false,
            (new SonarEnvVar())->enabled(new FakeConfig([])),
            'SonarEnvVar must be disabled when sonar.cloud key is absent (default is cloud mode)',
        );
    }

    #[Test]
    public function enabledWhenCloudFalse(): void
    {
        self::assertSame(
            true,
            (new SonarEnvVar())->enabled(new FakeConfig(['sonar.cloud' => [false]])),
            'SonarEnvVar must be enabled when sonar.cloud is false (local scanner mode)',
        );
    }

    #[Test]
    public function disabledWhenCloudFalseAndSonarDisabled(): void
    {
        self::assertSame(
            false,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => [false],
                'sonar.cli' => [false],
            ])),
            'SonarEnvVar must be disabled when sonar.cli is false even in scanner mode',
        );
    }

    #[Test]
    public function enabledWhenCloudFalseAndSonarEnabled(): void
    {
        self::assertSame(
            true,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => [false],
                'sonar.cli' => [true],
            ])),
            'SonarEnvVar must be enabled when sonar.cloud is false and sonar.cli is true',
        );
    }

    #[Test]
    public function disabledWhenCloudTrueAndSonarDisabled(): void
    {
        self::assertSame(
            false,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => [true],
                'sonar.cli' => [false],
            ])),
            'SonarEnvVar must be disabled when sonar.cloud is true regardless of sonar.cli',
        );
    }

    #[Test]
    public function disabledWhenCloudFalseStringAndSonarDisabledString(): void
    {
        self::assertSame(
            false,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => ['false'],
                'sonar.cli' => ['false'],
            ])),
            'SonarEnvVar must handle string "false" for both keys',
        );
    }

    #[Test]
    public function enabledWhenCloudFalseAndCliKeyPresentButEmpty(): void
    {
        self::assertSame(
            true,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => [false],
                'sonar.cli' => [],
            ])),
            'SonarEnvVar must be enabled when sonar.cli is present but list is empty',
        );
    }

    #[Test]
    public function enabledWhenCloudFalseAndCliValueNotParsableAsBoolean(): void
    {
        self::assertSame(
            true,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => [false],
                'sonar.cli' => ['maybe'],
            ])),
            'SonarEnvVar must be enabled when sonar.cli value cannot be parsed as boolean',
        );
    }

    #[Test]
    public function disabledWhenCloudKeyPresentButEmpty(): void
    {
        self::assertSame(
            false,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => [],
            ])),
            'SonarEnvVar must treat empty sonar.cloud list as cloud enabled and be disabled',
        );
    }

    #[Test]
    public function disabledWhenCloudValueNotParsableAsBoolean(): void
    {
        self::assertSame(
            false,
            (new SonarEnvVar())->enabled(new FakeConfig([
                'sonar.cloud' => ['maybe'],
            ])),
            'SonarEnvVar must treat non-parsable sonar.cloud as true (cloud mode) and be disabled',
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
