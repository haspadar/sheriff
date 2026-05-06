<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config;

use Haspadar\Sheriff\Config\ConfigPaths;
use Haspadar\Sheriff\Config\DefaultConfig;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DefaultConfigTest extends TestCase
{
    #[Test]
    public function returnsTrueWhenKeyIsDeclared(): void
    {
        self::assertTrue(
            (new DefaultConfig())->has('shellcheck.shell'),
            'DefaultConfig must report declared keys as present',
        );
    }

    #[Test]
    public function returnsFalseWhenKeyIsNotDeclared(): void
    {
        self::assertFalse(
            (new DefaultConfig())->has('unknown.key'),
            'DefaultConfig must report unknown keys as absent',
        );
    }

    #[Test]
    public function returnsScalarDefaultAsListWhenKeyDeclared(): void
    {
        self::assertSame(
            ['bash'],
            (new DefaultConfig())->list('shellcheck.shell'),
            'scalar default must be wrapped in a list',
        );
    }

    #[Test]
    public function returnsListDefaultWhenKeyDeclared(): void
    {
        self::assertSame(
            ['../../tests/Unit'],
            (new DefaultConfig())->list('phpunit.testsuites.unit'),
            'list default must be returned as-is',
        );
    }

    #[Test]
    public function returnsNumericCoverageDefaultAsListWhenKeyDeclared(): void
    {
        self::assertSame(
            [80],
            (new DefaultConfig())->list('coverage.project.target'),
            'numeric coverage default must be wrapped in a list without percent sign',
        );
    }

    #[Test]
    public function returnsVendorBinaryPathWhenSheriffRunsInCi(): void
    {
        self::assertSame(
            ['vendor/bin/sheriff'],
            (new DefaultConfig())->list('ci.sheriff_bin'),
            'CI must run the Composer-installed sheriff binary by default',
        );
    }

    #[Test]
    public function returnsEmptyListWhenKeyIsUnknown(): void
    {
        self::assertSame(
            [],
            (new DefaultConfig())->list('unknown.key'),
            'DefaultConfig must return empty list for unknown keys without validation',
        );
    }

    #[Test]
    public function returnsTrueForToolEnabledByDefault(): void
    {
        self::assertSame(
            [true],
            (new DefaultConfig())->list('hadolint.cli'),
            'hadolint must be enabled by default',
        );
    }

    #[Test]
    public function defaultsPhpSrcToSrc(): void
    {
        self::assertSame(
            ['src'],
            (new DefaultConfig())->list('php.src'),
            'php.src must default to src',
        );
    }

    #[Test]
    public function defaultsInfraExcludeToVendorTestsGit(): void
    {
        self::assertSame(
            ['vendor', 'tests', '.git'],
            (new DefaultConfig())->list('infra.exclude'),
            'infra.exclude must default to vendor, tests, and .git',
        );
    }

    #[Test]
    public function defaultsPhpVersionsTo83(): void
    {
        self::assertSame(
            ['8.3'],
            (new DefaultConfig())->list('php.versions'),
            'php.versions must default to 8.3',
        );
    }

    #[Test]
    public function returnsToArrayWithAllDeclaredKeys(): void
    {
        $array = (new DefaultConfig())->toArray();

        self::assertArrayHasKey(
            'phpstan.level',
            $array,
            'toArray must include all declared default keys',
        );
    }

    #[Test]
    public function throwsWhenConfigYamlHasNoDefaultsSection(): void
    {
        $folder = (new TempFolder())->withFile('empty.yaml', 'root: true');

        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Missing "defaults" section');

        $config = new DefaultConfig(paths: new ConfigPaths(config: $folder->path() . '/empty.yaml'));

        try {
            $config->has('any');
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function throwsWhenConfigYamlContainsInvalidSyntax(): void
    {
        $folder = (new TempFolder())->withFile('broken.yaml', ": invalid: yaml: :");

        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Failed to parse config');

        $config = new DefaultConfig(paths: new ConfigPaths(config: $folder->path() . '/broken.yaml'));

        try {
            $config->has('any');
        } finally {
            $folder->close();
        }
    }
}
