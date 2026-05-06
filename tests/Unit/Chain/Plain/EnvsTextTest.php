<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Plain;

use Haspadar\Sheriff\Chain\Plain\EnvsText;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnvsTextTest extends TestCase
{
    #[Test]
    public function rendersEmptyStringForEmptyTree(): void
    {
        self::assertSame(
            '',
            (new EnvsText(new TreeValue([]), '      '))->rendered(),
            'EnvsText must render an empty string when the envs tree is empty',
        );
    }

    #[Test]
    public function rendersEmptyStringForEmptyListSinceYamlEmptyMappingParsesAsList(): void
    {
        self::assertSame(
            '',
            (new EnvsText(new ListValue([]), '      '))->rendered(),
            'EnvsText must accept an empty ListValue payload because YAML `{}` parses as `[]`',
        );
    }

    #[Test]
    public function rendersWorkflowStepWithFetchPrologueAndEchoLines(): void
    {
        self::assertSame(
            "      - name: Set environment variables\n"
            . "        run: |\n"
            . "          git fetch --tags --unshallow 2>/dev/null || git fetch --tags\n"
            . '          echo "COMPOSER_ROOT_VERSION=$(git describe)" >> "$GITHUB_ENV"',
            (new EnvsText(
                new TreeValue([
                    'COMPOSER_ROOT_VERSION' => new StringValue('git describe'),
                ]),
                '      ',
            ))->rendered(),
            'EnvsText must render the GHA step with the fetch prologue and one echo line per env var',
        );
    }

    #[Test]
    public function rejectsInvalidEnvironmentVariableName(): void
    {
        $this->expectException(SheriffException::class);

        (new EnvsText(
            new TreeValue(['1BAD' => new StringValue('cmd')]),
            '      ',
        ))->rendered();
    }

    #[Test]
    public function rejectsNonStringValueCommand(): void
    {
        $this->expectException(SheriffException::class);

        (new EnvsText(
            new TreeValue(['VAR' => new TreeValue([])]),
            '      ',
        ))->rendered();
    }

    #[Test]
    public function rejectsNonEmptyListPayload(): void
    {
        $this->expectException(SheriffException::class);

        (new EnvsText(
            new ListValue([new StringValue('x')]),
            '      ',
        ))->rendered();
    }

    #[Test]
    public function rendersEveryTreeEntryAsItsOwnEchoLine(): void
    {
        self::assertSame(
            "      - name: Set environment variables\n"
            . "        run: |\n"
            . "          git fetch --tags --unshallow 2>/dev/null || git fetch --tags\n"
            . '          echo "FOO=$(echo a)" >> "$GITHUB_ENV"' . "\n"
            . '          echo "BAR=$(echo b)" >> "$GITHUB_ENV"',
            (new EnvsText(
                new TreeValue([
                    'FOO' => new StringValue('echo a'),
                    'BAR' => new StringValue('echo b'),
                ]),
                '      ',
            ))->rendered(),
            'EnvsText must render one echo line per env-var while keeping the shared fetch prologue',
        );
    }

    #[Test]
    public function rendersWithoutLeadingIndentWhenIndentIsEmpty(): void
    {
        self::assertSame(
            "- name: Set environment variables\n"
            . "  run: |\n"
            . "    git fetch --tags --unshallow 2>/dev/null || git fetch --tags\n"
            . '    echo "FOO=$(cmd)" >> "$GITHUB_ENV"',
            (new EnvsText(
                new TreeValue(['FOO' => new StringValue('cmd')]),
                '',
            ))->rendered(),
            'EnvsText must apply an empty indent verbatim, leaving the inner four-space step indent intact',
        );
    }
}
