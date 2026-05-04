<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Formula\Actions;

use Haspadar\Sheriff\Formula\Action\FormatEachAction;
use Haspadar\Sheriff\Formula\Action\JoinAction;
use Haspadar\Sheriff\Formula\Actions\ParsedActions;
use Haspadar\Sheriff\Tests\Constraint\Formula\Actions\HasActionNames;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParsedActionsTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenExpressionHasNoActions(): void
    {
        self::assertThat(
            new ParsedActions('', []),
            new HasActionNames([]),
            'ParsedActions must return an empty list when the expression is empty',
        );
    }

    #[Test]
    public function parsesSingleAction(): void
    {
        self::assertThat(
            new ParsedActions(
                'format_each("x=%s")',
                [
                    'format_each' => fn(string $raw) => new FormatEachAction($raw),
                ],
            ),
            new HasActionNames([
                FormatEachAction::class,
            ]),
            'ParsedActions must parse a single action from the expression',
        );
    }

    #[Test]
    public function preservesActionOrder(): void
    {
        self::assertThat(
            new ParsedActions(
                'format_each("v=%s")|join(",")',
                [
                    'format_each' => fn(string $raw) => new FormatEachAction($raw),
                    'join' => fn(string $raw) => new JoinAction($raw),
                ],
            ),
            new HasActionNames([
                FormatEachAction::class,
                JoinAction::class,
            ]),
            'ParsedActions must preserve the order of actions as they appear in the expression',
        );
    }
}
