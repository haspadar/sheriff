<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config;

use Haspadar\Sheriff\Config\DefaultConfig;
use Haspadar\Sheriff\Config\YamlConfig;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
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
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile('.sheriff.yaml', ": invalid: yaml: :")->path() . '/.sheriff.yaml';

        (new YamlConfig($path, new DefaultConfig()))->has('');
    }

    #[Test]
    public function throwsWhenFileContainsScalarYaml(): void
    {
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile('.sheriff.yaml', "just a string\n")->path() . '/.sheriff.yaml';

        (new YamlConfig($path, new DefaultConfig()))->has('');
    }

    #[Test]
    public function returnsDelegatedHasWhenNoOverrides(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "override: {}\n")->path() . '/.sheriff.yaml';

        self::assertTrue(
            (new YamlConfig($path, new DefaultConfig()))->has('phpstan.level'),
            'YamlConfig must delegate has() to defaults when no overrides are present',
        );
    }

    #[Test]
    public function returnsDefaultValueWhenNoOverridesOrAppends(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "{}\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            [9],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.level'),
            'YamlConfig must return the default value when no overrides or appends are specified',
        );
    }

    #[Test]
    public function returnsOverriddenValueWhenOverrideSectionPresent(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    phpstan.level: 7\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            [7],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.level'),
            'YamlConfig must return the overridden value when override section is present',
        );
    }

    #[Test]
    public function returnsSheriffBinaryWhenSheriffBinaryIsOverridden(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    ci.sheriff_bin: bin/sheriff\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            ['bin/sheriff'],
            (new YamlConfig($path, new DefaultConfig()))->list('ci.sheriff_bin'),
            'YamlConfig must return a direct CI binary override',
        );
    }

    #[Test]
    public function returnsSheriffBinaryListWhenSheriffBinaryListIsOverridden(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    ci.sheriff_bin:\n        - bin/sheriff\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            ['bin/sheriff'],
            (new YamlConfig($path, new DefaultConfig()))->list('ci.sheriff_bin'),
            'YamlConfig must return a direct CI binary list override',
        );
    }

    #[Test]
    public function preservesSiblingOverrideWhenSheriffBinaryIsOverridden(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    ci.sheriff_bin: bin/sheriff\n    phpstan.level: 7\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            [7],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.level'),
            'YamlConfig must preserve sibling overrides when reading the CI binary key',
        );
    }

    #[Test]
    public function throwsWhenSheriffBinaryOverrideContainsNestedList(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Override "ci.sheriff_bin" must contain only scalars');

        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    ci.sheriff_bin:\n        - [bin/sheriff]\n")->path() . '/.sheriff.yaml';

        (new YamlConfig($path, new DefaultConfig()))->list('ci.sheriff_bin');
    }

    #[Test]
    public function throwsWhenSheriffBinaryOverrideIsMapping(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Override "ci.sheriff_bin" must be scalar or list<scalar>');

        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    ci.sheriff_bin:\n        path: bin/sheriff\n")->path() . '/.sheriff.yaml';

        (new YamlConfig($path, new DefaultConfig()))->list('ci.sheriff_bin');
    }

    #[Test]
    public function returnsAppendedValuesWhenAppendSectionPresent(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "append:\n    phpstan.neon_includes:\n        - ../../rules.neon\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            ['../../vendor/phpstan/phpstan-strict-rules/rules.neon', '../../vendor/haspadar/phpstan-rules/rules.neon', '../../rules.neon'],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.neon_includes'),
            'YamlConfig must append values from the append section to the default list',
        );
    }

    #[Test]
    public function appendsToExistingDefaultList(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "append:\n    phpstan.neon_includes:\n        - ../../extra.neon\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            ['../../vendor/phpstan/phpstan-strict-rules/rules.neon', '../../vendor/haspadar/phpstan-rules/rules.neon', '../../extra.neon'],
            (new YamlConfig($path, new DefaultConfig()))->list('phpstan.neon_includes'),
            'YamlConfig must preserve default values and append new ones after them',
        );
    }

    #[Test]
    public function toArrayIncludesOverriddenValue(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    phpstan.level: 7\n")->path() . '/.sheriff.yaml';

        self::assertSame(
            [7],
            (new YamlConfig($path, new DefaultConfig()))->toArray()['phpstan.level'],
            'toArray must reflect overridden phpstan.level',
        );
    }

    #[Test]
    public function returnsCachedConfigOnRepeatedAccess(): void
    {
        $path = $this->folder->withFile('.sheriff.yaml', "override:\n    phpstan.level: 7\n")->path() . '/.sheriff.yaml';
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
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile('.sheriff.yaml', "{}\n")->path() . '/.sheriff.yaml';

        (new YamlConfig($path, new DefaultConfig()))->list('phpstan.nonexistent');
    }
}
