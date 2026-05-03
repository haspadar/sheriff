<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Unit\Config;

use Haspadar\Piqule\Config\DefaultConfig;
use Haspadar\Piqule\Config\YamlConfig;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class YamlConfigTest extends TestCase
{
    private TempFolder $folder;

    protected function setUp(): void
    {
        $this->folder = new TempFolder();
    }

    protected function tearDown(): void
    {
        $this->folder->close();
    }

    #[Test]
    public function throwsWhenFileContainsInvalidYaml(): void
    {
        $this->expectException(PiquleException::class);

        $path = $this->folder->withFile('.piqule.yaml', ": invalid: yaml: :")->path() . '/.piqule.yaml';

        (new YamlConfig($path, new DefaultConfig()))->has('');
    }

    #[Test]
    public function throwsWhenFileContainsScalarYaml(): void
    {
        $this->expectException(PiquleException::class);

        $path = $this->folder->withFile('.piqule.yaml', "just a string\n")->path() . '/.piqule.yaml';

        (new YamlConfig($path, new DefaultConfig()))->has('');
    }

    #[Test]
    public function returnsDelegatedHasWhenNoOverrides(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "override: {}\n")->path() . '/.piqule.yaml';

        self::assertTrue(
            (new YamlConfig($path, new DefaultConfig()))->has('phpstan.level'),
            'YamlConfig must delegate has() to defaults when no overrides are present',
        );
    }

    #[Test]
    public function returnsDefaultValueWhenNoOverridesOrAppends(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "{}\n")->path() . '/.piqule.yaml';

        self::assertSame(
            [9],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.level'),
            'YamlConfig must return the default value when no overrides or appends are specified',
        );
    }

    #[Test]
    public function returnsOverriddenValueWhenOverrideSectionPresent(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "override:\n    phpstan.level: 7\n")->path() . '/.piqule.yaml';

        self::assertSame(
            [7],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.level'),
            'YamlConfig must return the overridden value when override section is present',
        );
    }

    #[Test]
    public function returnsSheriffBinaryWhenLegacyPiquleBinaryIsOverridden(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "override:\n    ci.piqule_bin: bin/sheriff\n")->path() . '/.piqule.yaml';

        self::assertSame(
            ['bin/sheriff'],
            (new YamlConfig($path, new DefaultConfig()))->list('ci.sheriff_bin'),
            'YamlConfig must map the legacy CI binary override to the Sheriff key',
        );
    }

    #[Test]
    public function returnsAppendedValuesWhenAppendSectionPresent(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "append:\n    phpstan.neon_includes:\n        - ../../rules.neon\n")->path() . '/.piqule.yaml';

        self::assertSame(
            ['../../vendor/phpstan/phpstan-strict-rules/rules.neon', '../../vendor/haspadar/phpstan-rules/rules.neon', '../../rules.neon'],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.neon_includes'),
            'YamlConfig must append values from the append section to the default list',
        );
    }

    #[Test]
    public function appendsToExistingDefaultList(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "append:\n    phpstan.neon_includes:\n        - ../../extra.neon\n")->path() . '/.piqule.yaml';

        self::assertSame(
            ['../../vendor/phpstan/phpstan-strict-rules/rules.neon', '../../vendor/haspadar/phpstan-rules/rules.neon', '../../extra.neon'],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.neon_includes'),
            'YamlConfig must preserve default values and append new ones after them',
        );
    }

    #[Test]
    public function toArrayIncludesOverriddenValue(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "override:\n    phpstan.level: 7\n")->path() . '/.piqule.yaml';

        self::assertSame(
            [7],
            (new YamlConfig($path, new DefaultConfig()))->toArray()['phpstan.level'],
            'toArray must reflect overridden phpstan.level',
        );
    }

    #[Test]
    public function returnsCachedConfigOnRepeatedAccess(): void
    {
        $path = $this->folder->withFile('.piqule.yaml', "override:\n    phpstan.level: 7\n")->path() . '/.piqule.yaml';
        $config = new YamlConfig($path, new DefaultConfig());
        $config->has('phpstan.level');

        self::assertSame(
            [7],
            $config->list('phpstan.level'),
            'repeated access must return the same cached result',
        );
    }

    #[Test]
    public function throwsWhenListCalledForUndeclaredKey(): void
    {
        $this->expectException(PiquleException::class);

        $path = $this->folder->withFile('.piqule.yaml', "{}\n")->path() . '/.piqule.yaml';

        (new YamlConfig($path, new DefaultConfig()))->list('phpstan.nonexistent');
    }
}
