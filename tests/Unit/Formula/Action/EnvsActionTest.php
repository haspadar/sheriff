<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Action;

use Haspadar\Sheriff\Envs\EmptyEnvs;
use Haspadar\Sheriff\Formula\Action\EnvsAction;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Tests\Constraint\Formula\Args\HasArgsValues;
use Haspadar\Sheriff\Tests\Fake\Envs\FakeEnvs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnvsActionTest extends TestCase
{
    #[Test]
    public function rendersStepWithSingleVariable(): void
    {
        self::assertThat(
            (new EnvsAction(
                new FakeEnvs(['MY_VAR' => 'echo hello']),
                '"      "',
            ))->transformed(new ListArgs([])),
            new HasArgsValues([
                "      - name: Set environment variables\n"
                . "        run: |\n"
                . "          git fetch --tags --unshallow 2>/dev/null || git fetch --tags\n"
                . '          echo "MY_VAR=$(echo hello)" >> "$GITHUB_ENV"',
            ]),
            'EnvsAction must render a step that exports one variable',
        );
    }

    #[Test]
    public function rendersStepWithMultipleVariables(): void
    {
        self::assertThat(
            (new EnvsAction(
                new FakeEnvs(['A' => 'cmd-a', 'B' => 'cmd-b']),
                '"      "',
            ))->transformed(new ListArgs([])),
            new HasArgsValues([
                "      - name: Set environment variables\n"
                . "        run: |\n"
                . "          git fetch --tags --unshallow 2>/dev/null || git fetch --tags\n"
                . "          echo \"A=\$(cmd-a)\" >> \"\$GITHUB_ENV\"\n"
                . '          echo "B=$(cmd-b)" >> "$GITHUB_ENV"',
            ]),
            'EnvsAction must render a step that exports multiple variables',
        );
    }

    #[Test]
    public function rendersEmptyStringWhenNoVariables(): void
    {
        self::assertThat(
            (new EnvsAction(
                new EmptyEnvs(),
                '"      "',
            ))->transformed(new ListArgs([])),
            new HasArgsValues(['']),
            'EnvsAction must return empty string when no env vars are configured',
        );
    }
}
