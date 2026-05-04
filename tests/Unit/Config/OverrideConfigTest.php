<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config;

use Haspadar\Sheriff\Config\OverrideConfig;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Fake\Config\FakeConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class OverrideConfigTest extends TestCase
{
    #[Test]
    public function returnsTrueWhenKeyIsDeclared(): void
    {
        self::assertTrue(
            (new OverrideConfig(
                new FakeConfig(['php.versions' => ['8.3']]),
                ['php.versions' => '8.4'],
            ))->has('php.versions'),
            'OverrideConfig must report a declared key as present',
        );
    }

    #[Test]
    public function returnsFalseWhenKeyIsNotDeclared(): void
    {
        self::assertFalse(
            (new OverrideConfig(
                new FakeConfig(['phpstan.level' => ['8']]),
                ['phpstan.level' => '9'],
            ))->has('phpstan.memory'),
            'OverrideConfig must report an undeclared key as absent',
        );
    }

    #[Test]
    public function returnsDefaultValueWhenKeyIsNotOverridden(): void
    {
        self::assertSame(
            ['8'],
            (new OverrideConfig(
                new FakeConfig(['phpstan.level' => ['8']]),
                [],
            ))->list('phpstan.level'),
            'OverrideConfig must return the default value when the key is not overridden',
        );
    }

    #[Test]
    public function returnsOverriddenScalarValueWhenKeyIsOverridden(): void
    {
        self::assertSame(
            ['error'],
            (new OverrideConfig(
                new FakeConfig(['shellcheck.severity' => ['warning']]),
                ['shellcheck.severity' => 'error'],
            ))->list('shellcheck.severity'),
            'OverrideConfig must return the overridden scalar value as a single-item list',
        );
    }

    #[Test]
    public function returnsOverriddenListValueWhenKeyIsOverridden(): void
    {
        self::assertSame(
            ['Unit', 'Integration'],
            (new OverrideConfig(
                new FakeConfig(['phpunit.testsuites.unit' => ['Unit']]),
                ['phpunit.testsuites.unit' => ['Unit', 'Integration']],
            ))->list('phpunit.testsuites.unit'),
            'OverrideConfig must return the overridden list value as-is',
        );
    }

    #[Test]
    public function throwsWhenListCalledForUndeclaredKey(): void
    {
        $this->expectException(SheriffException::class);

        (new OverrideConfig(
            new FakeConfig([]),
            [],
        ))->list('phpmetrics.size.max_loc_per_class');
    }

    #[Test]
    public function wrapsJsonlintCompactBooleanIntoList(): void
    {
        self::assertSame(
            [true],
            (new OverrideConfig(
                new FakeConfig(['jsonlint.compact' => [false]]),
                ['jsonlint.compact' => true],
            ))->list('jsonlint.compact'),
            'Boolean overrides must be normalized to a single-item scalar list.',
        );
    }

    #[Test]
    public function throwsWhenYamlOverrideIsAssociative(): void
    {
        $this->expectException(SheriffException::class);

        (new OverrideConfig(
            new FakeConfig(['hadolint.override.error_yaml' => []]),
            ['hadolint.override.error_yaml' => ['DL3008' => 'ignore']],
        ))->list('hadolint.override.error_yaml');
    }

    #[Test]
    public function throwsWhenMutationTimeoutContainsObject(): void
    {
        $this->expectException(SheriffException::class);

        (new OverrideConfig(
            new FakeConfig(['infection.timeout' => []]),
            ['infection.timeout' => [new stdClass()]],
        ))->list('infection.timeout');
    }

    #[Test]
    public function throwsWhenOverrideIsObject(): void
    {
        $this->expectException(SheriffException::class);

        (new OverrideConfig(
            new FakeConfig(['jsonlint.mode' => []]),
            ['jsonlint.mode' => new stdClass()],
        ))->list('jsonlint.mode');
    }

    #[Test]
    public function toArrayReturnsAllKeysWithOverridesApplied(): void
    {
        self::assertSame(
            ['phpstan.level' => [9], 'phpstan.paths' => ['src']],
            (new OverrideConfig(
                new FakeConfig(['phpstan.level' => ['8'], 'phpstan.paths' => ['src']]),
                ['phpstan.level' => 9],
            ))->toArray(),
            'toArray must return all keys with overrides applied',
        );
    }
}
