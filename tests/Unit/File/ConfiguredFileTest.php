<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\File;

use Haspadar\Sheriff\Config\Config;
use Haspadar\Sheriff\Config\DefaultConfig;
use Haspadar\Sheriff\Config\OverrideConfig;
use Haspadar\Sheriff\Envs\EmptyEnvs;
use Haspadar\Sheriff\File\ConfiguredFile;
use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Formula\Action\Action;
use Haspadar\Sheriff\Formula\Actions\FormulaActions;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFileContents;
use Haspadar\Sheriff\Tests\Constraint\HasFormulaError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfiguredFileTest extends TestCase
{
    #[Test]
    public function replacesPlaceholderUsingPipeline(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php.versions' => ['8.3', '8.4']],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(php.versions)|format_each("%s")|join(",") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('8.3,8.4'),
            'placeholder must resolve to joined matrix values',
        );
    }

    #[Test]
    public function leavesFileUntouchedWhenNoPlaceholdersPresent(): void
    {
        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'plain.txt',
                    "just text\nno placeholders here",
                ),
                $this->actions(new OverrideConfig(new DefaultConfig(), [])),
            ),
            new HasFileContents("just text\nno placeholders here"),
            'file without placeholders must be returned unchanged',
        );
    }

    #[Test]
    public function resolvesDotSeparatedConfigKey(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['coverage.patch.target' => 85],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'config.yaml',
                    'coverage: << config(coverage.patch.target)|format("%s%%") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('coverage: 85%'),
        );
    }

    #[Test]
    public function usesDefaultValueWhenKeyNotOverridden(): void
    {
        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'broken.yaml',
                    'value: << config(shellcheck.shell)|join("") >>',
                ),
                $this->actions(new OverrideConfig(new DefaultConfig(), [])),
            ),
            new HasFileContents('value: bash'),
        );
    }

    #[Test]
    public function wrapsUnknownActionWithFileContext(): void
    {
        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'broken.yaml',
                    '<< unknown(a) >>',
                ),
                $this->actions(new OverrideConfig(new DefaultConfig(), [])),
            ),
            new HasFormulaError(
                'broken.yaml',
                'unknown(a)',
                'Unknown formula action',
            ),
        );
    }

    #[Test]
    public function throwsWhenFormulaProducesMultipleValues(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php.versions' => ['8.3', '8.4']],
        );

        $this->expectException(SheriffException::class);

        (new ConfiguredFile(
            new TextFile(
                'file',
                '<< config(php.versions) >>',
            ),
            $this->actions($config),
        ))->contents();
    }

    #[Test]
    public function throwsWhenFirstActionReceivesArguments(): void
    {
        $this->expectException(SheriffException::class);

        (new ConfiguredFile(
            new TextFile(
                'file',
                '<< config(shellcheck.shell)|first(something) >>',
            ),
            $this->actions(new OverrideConfig(new DefaultConfig(), [])),
        ))->contents();
    }

    #[Test]
    public function rendersContentWhenIfNotEmptyReceivesNonEmptyInput(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php.versions' => ['8.3', '8.4']],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(php.versions)|format_each("%s")|join(",")|if_not_empty()|format("[%s]") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('[8.3,8.4]'),
            'if_not_empty must pass non-empty value through to format',
        );
    }

    #[Test]
    public function rendersEmptyWhenIfNotEmptyReceivesEmptyInput(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['psalm.project.files' => []],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    'before|<< config(psalm.project.files)|format_each("%s")|join(",")|if_not_empty() >>|after',
                ),
                $this->actions($config),
            ),
            new HasFileContents('before||after'),
            'if_not_empty must produce empty string for empty config list',
        );
    }

    #[Test]
    public function rendersContentWhenIfEmptyReceivesEmptyInput(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['psalm.project.files' => []],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(psalm.project.files)|join(",")|if_empty()|format("none") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('none'),
            'if_empty must pass empty value through to format',
        );
    }

    #[Test]
    public function rendersEmptyWhenIfEmptyReceivesNonEmptyInput(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php.versions' => ['8.3']],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    'x<< config(php.versions)|join(",")|if_empty() >>y',
                ),
                $this->actions($config),
            ),
            new HasFileContents('xy'),
            'if_empty must produce empty string for non-empty config list',
        );
    }

    #[Test]
    public function throwsWhenIfNotEmptyReceivesArguments(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Action "if_not_empty" does not accept arguments');

        (new ConfiguredFile(
            new TextFile(
                'file',
                '<< config(shellcheck.shell)|if_not_empty(something) >>',
            ),
            $this->actions(new OverrideConfig(new DefaultConfig(), [])),
        ))->contents();
    }

    #[Test]
    public function throwsWhenIfEmptyReceivesArguments(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Action "if_empty" does not accept arguments');

        (new ConfiguredFile(
            new TextFile(
                'file',
                '<< config(shellcheck.shell)|if_empty(something) >>',
            ),
            $this->actions(new OverrideConfig(new DefaultConfig(), [])),
        ))->contents();
    }

    #[Test]
    public function throwsWhenShellQuoteReceivesArguments(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Action "shell_quote" does not accept arguments');

        (new ConfiguredFile(
            new TextFile(
                'file',
                '<< config(shellcheck.shell)|shell_quote(something) >>',
            ),
            $this->actions(new OverrideConfig(new DefaultConfig(), [])),
        ))->contents();
    }

    #[Test]
    public function throwsWhenJsonEscapeReceivesArguments(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('Action "json_escape" does not accept arguments');

        (new ConfiguredFile(
            new TextFile(
                'file',
                '<< config(shellcheck.shell)|json_escape(something) >>',
            ),
            $this->actions(new OverrideConfig(new DefaultConfig(), [])),
        ))->contents();
    }

    #[Test]
    public function acceptsWhitespaceOnlyArgumentForFirst(): void
    {
        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(shellcheck.shell)|first( ) >>',
                ),
                $this->actions(new OverrideConfig(new DefaultConfig(), [])),
            ),
            new HasFileContents('bash'),
            'first() must accept whitespace-only argument as empty',
        );
    }

    #[Test]
    public function acceptsWhitespaceOnlyArgumentForIfEmpty(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['psalm.project.files' => []],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(psalm.project.files)|join(",")|if_empty(  )|format("none") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('none'),
            'if_empty() must accept whitespace-only argument as empty',
        );
    }

    #[Test]
    public function acceptsWhitespaceOnlyArgumentForIfNotEmpty(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php.versions' => ['8.3']],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(php.versions)|join(",")|if_not_empty(  )|format("[%s]") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('[8.3]'),
            'if_not_empty() must accept whitespace-only argument as empty',
        );
    }

    #[Test]
    public function appliesReplaceWithinPipeline(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php.versions' => ['8.3', '8.4']],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(php.versions)|replace(".", "x")|format_each("@PHP%sMigration") |join(",") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('@PHP8x3Migration,@PHP8x4Migration'),
            'replace must slug each version before formatting',
        );
    }

    #[Test]
    public function emitsSingleMigrationRuleSetForSinglePhpVersion(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php.versions' => ['8.3']],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'file',
                    '<< config(php.versions)|replace(".", "x")|format_each("@PHP%sMigration")|join(",") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('@PHP8x3Migration'),
            'single version must not produce rule sets for absent versions',
        );
    }

    #[Test]
    public function rendersPhpCsFixerExtendAsRawString(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['php_cs_fixer.extend' => "        'phpdoc_types' => ['exclude' => ['scalar']],"],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'php-cs-fixer.php',
                    '<< config(php_cs_fixer.extend)|join("") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents("        'phpdoc_types' => ['exclude' => ['scalar']],"),
            'php_cs_fixer.extend must pass the raw PHP fragment through to the template',
        );
    }

    #[Test]
    public function rendersPhpcsExtendAsRawString(): void
    {
        $config = new OverrideConfig(
            new DefaultConfig(),
            ['phpcs.extend' => '    <rule ref="Foo.Bar"><severity>0</severity></rule>'],
        );

        self::assertThat(
            new ConfiguredFile(
                new TextFile(
                    'phpcs.xml',
                    '<< config(phpcs.extend)|join("") >>',
                ),
                $this->actions($config),
            ),
            new HasFileContents('    <rule ref="Foo.Bar"><severity>0</severity></rule>'),
            'phpcs.extend must pass the raw XML fragment through to the template',
        );
    }

    #[Test]
    public function preservesOriginMode(): void
    {
        $file = new ConfiguredFile(
            new TextFile(
                'file',
                '<< config(shellcheck.shell)|join("") >>',
                0o755,
            ),
            $this->actions(new OverrideConfig(new DefaultConfig(), [])),
        );

        self::assertSame(0o755, $file->mode(), 'ConfiguredFile must preserve the origin file mode');
    }

    /** @return array<string, callable(string): Action> */
    private function actions(Config $config): array
    {
        return (new FormulaActions($config, new EmptyEnvs()))->map();
    }
}
