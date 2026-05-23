<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\File;

use Haspadar\Sheriff\File\TemplateFile;
use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFileContents;
use Haspadar\Sheriff\Tests\Constraint\HasFormulaFailure;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TemplateFileTest extends TestCase
{
    #[Test]
    public function replacesPlaceholderUsingChainPipeline(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile(
                    'phpstan.neon',
                    'paths: {% ListText(phpstan.paths)|EachFormatted("- %s")|Joined("\n") %}',
                ),
                new FakeSettings([
                    'phpstan.paths' => new ListValue([
                        new StringValue('src'),
                        new StringValue('tests'),
                    ]),
                ]),
            ),
            new HasFileContents("paths: - src\n- tests"),
            'TemplateFile must render Chain placeholders through PipelineOp',
        );
    }

    #[Test]
    public function leavesFileUntouchedWhenNoPlaceholdersPresent(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('plain.txt', "plain\ntext"),
                new FakeSettings([]),
            ),
            new HasFileContents("plain\ntext"),
            'TemplateFile must leave files without Chain placeholders unchanged',
        );
    }

    #[Test]
    public function keepsOriginalMode(): void
    {
        self::assertSame(
            0o755,
            (new TemplateFile(
                new TextFile('script.sh', '#!/bin/sh', 0o755),
                new FakeSettings([]),
            ))->mode(),
            'TemplateFile must preserve wrapped file mode',
        );
    }

    #[Test]
    public function wrapsPipelineErrorsWithFileContext(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('broken.neon', '{% MissingFormula(phpstan.paths) %}'),
                new FakeSettings([]),
            ),
            new HasFormulaFailure(
                'broken.neon',
                'MissingFormula(phpstan.paths)',
                'Unknown pipeline formula',
            ),
        );
    }

    #[Test]
    public function wrapsMissingSettingErrorsWithFileContext(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('missing.neon', '{% StringText(app.name) %}'),
                new FakeSettings([]),
            ),
            new HasFormulaFailure(
                'missing.neon',
                'StringText(app.name)',
                'cannot find settings key',
            ),
        );
    }

    #[Test]
    public function wrapsTypeErrorsWithFileContext(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('typed.neon', '{% StringText(phpstan.paths) %}'),
                new FakeSettings([
                    'phpstan.paths' => new ListValue([
                        new StringValue('src'),
                    ]),
                ]),
            ),
            new HasFormulaFailure(
                'typed.neon',
                'StringText(phpstan.paths)',
                'StringValue',
            ),
        );
    }
}
