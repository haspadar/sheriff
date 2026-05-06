<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Config;

use Haspadar\Sheriff\Config\DefaultConfig;
use Haspadar\Sheriff\Config\YamlConfig;
use Haspadar\Sheriff\Tests\Constraint\Config\HasConfigYamlKey;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigYamlTemplateTest extends TestCase
{
    #[Test]
    public function defaultsAreAvailable(): void
    {
        self::assertThat(
            new DefaultConfig(),
            new HasConfigYamlKey('php.src', ['src']),
            'DefaultConfig must expose php.src default',
        );
    }

    #[Test]
    public function psalmProjectIgnoreIsEmptyByDefault(): void
    {
        self::assertThat(
            new DefaultConfig(),
            new HasConfigYamlKey('psalm.project.ignore', []),
            'psalm.project.ignore must be empty so .git is never passed as a <directory> to psalm',
        );
    }

    #[Test]
    public function overrideReplacesConfigValue(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "override:\n    phpstan.level: 7\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('phpstan.level', 7),
                'Override must replace phpstan.level with 7',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function appendAddsNewValueToInfraExclude(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "append:\n    infra.exclude:\n        - dist\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('infra.exclude', ['vendor', 'tests', '.git', 'dist']),
                'Append must add "dist" to infra.exclude',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function overrideInfraExcludeCascadesToDerivedKeys(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "override:\n    infra.exclude:\n        - dist\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('markdownlint.ignores', ['dist/**']),
                'Override infra.exclude must cascade to markdownlint.ignores',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function appendInfraExcludeCascadesToDerivedKeys(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "append:\n    infra.exclude:\n        - dist\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('markdownlint.ignores', ['vendor/**', 'tests/**', '.git/**', 'dist/**']),
                'Append infra.exclude must cascade to markdownlint.ignores',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function overridePhpSrcCascadesToDerivedKeys(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "override:\n    php.src:\n        - lib\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('phpmd.paths', ['lib']),
                'Override php.src must cascade to phpmd.paths',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function appendPhpSrcCascadesToDerivedKeys(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "append:\n    php.src:\n        - lib\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('phpmd.paths', ['src', 'lib']),
                'Append php.src must cascade to phpmd.paths',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function customIncludeCascadesToDerivedKeys(): void
    {
        self::assertThat(
            new DefaultConfig(['lib', 'app']),
            new HasConfigYamlKey('phpmd.paths', ['lib', 'app']),
            'Custom include must cascade to phpmd.paths',
        );
    }

    #[Test]
    public function phpCsFixerExtendIsEmptyByDefault(): void
    {
        self::assertThat(
            new DefaultConfig(),
            new HasConfigYamlKey('php_cs_fixer.extend', ['']),
            'php_cs_fixer.extend must be an empty string by default',
        );
    }

    #[Test]
    public function overridePhpCsFixerExtendStoresRawString(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "override:\n    php_cs_fixer.extend: \"'phpdoc_scalar' => false,\"\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('php_cs_fixer.extend', ["'phpdoc_scalar' => false,"]),
                'Override must store the extend scalar verbatim',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function phpcsExtendIsEmptyByDefault(): void
    {
        self::assertThat(
            new DefaultConfig(),
            new HasConfigYamlKey('phpcs.extend', ['']),
            'phpcs.extend must be an empty string by default',
        );
    }

    #[Test]
    public function overridePhpcsExtendStoresRawString(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "override:\n    phpcs.extend: \"<rule ref='Foo.Bar'><severity>0</severity></rule>\"\n",
        );

        try {
            self::assertThat(
                new YamlConfig($folder->path() . '/.sheriff.yaml', new DefaultConfig()),
                new HasConfigYamlKey('phpcs.extend', ["<rule ref='Foo.Bar'><severity>0</severity></rule>"]),
                'Override must store the extend scalar verbatim',
            );
        } finally {
            $folder->close();
        }
    }
}
