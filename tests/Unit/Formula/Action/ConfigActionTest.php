<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Config\DefaultConfig;
use Haspadar\Sheriff\Config\OverrideConfig;
use Haspadar\Sheriff\Formula\Action\ConfigAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigActionTest extends TestCase
{
    #[Test]
    public function returnsListValuesFromConfig(): void
    {
        self::assertThat(
            (new ConfigAction(
                new OverrideConfig(
                    new DefaultConfig(),
                    ['phpmetrics.extensions' => ['mbstring', 'intl']],
                ),
                'phpmetrics.extensions',
            ))->transformed(new ListArgs([])),
            new HasArgsValues(['mbstring', 'intl']),
            'ConfigAction must return list values from the config for the given key',
        );
    }

    #[Test]
    public function stringifiesBooleanValuesFromConfig(): void
    {
        self::assertThat(
            (new ConfigAction(
                new OverrideConfig(
                    new DefaultConfig(),
                    ['shellcheck.external_sources' => false],
                ),
                'shellcheck.external_sources',
            ))->transformed(new ListArgs([])),
            new HasArgsValues(['false']),
            'ConfigAction must convert boolean config values to their string representations',
        );
    }

    #[Test]
    public function returnsDefaultValuesWhenKeyNotOverridden(): void
    {
        self::assertThat(
            (new ConfigAction(
                new OverrideConfig(
                    new DefaultConfig(),
                    [],
                ),
                'shellcheck.exclude',
            ))->transformed(new ListArgs([])),
            new HasArgsValues([]),
            'ConfigAction must return the default config values when the key is not overridden',
        );
    }
}
