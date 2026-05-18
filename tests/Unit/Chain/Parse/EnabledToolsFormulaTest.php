<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Parse;

use Haspadar\Sheriff\Chain\Listed;
use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Chain\Parse\EnabledToolsFormula;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Haspadar\Sheriff\SheriffException;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnabledToolsFormulaTest extends TestCase
{
    #[Test]
    public function keepsToolWhenCliFlagTrue(): void
    {
        self::assertSame(
            ['actionlint'],
            self::names(
                (new EnabledToolsFormula(['ci.infra_checks']))
                    ->op([], self::settings([
                        'ci.infra_checks' => new ListValue([new StringValue('actionlint')]),
                        'actionlint.cli' => new BoolValue(true),
                    ])),
            ),
            'EnabledTools must keep names whose <name>.cli flag is true',
        );
    }

    #[Test]
    public function dropsToolWhenCliFlagFalse(): void
    {
        self::assertSame(
            ['phpstan'],
            self::names(
                (new EnabledToolsFormula(['ci.php_checks']))
                    ->op([], self::settings([
                        'ci.php_checks' => new ListValue([
                            new StringValue('phpstan'),
                            new StringValue('phpmetrics'),
                        ]),
                        'phpstan.cli' => new BoolValue(true),
                        'phpmetrics.cli' => new BoolValue(false),
                    ])),
            ),
            'EnabledTools must drop names whose <name>.cli flag is false',
        );
    }

    #[Test]
    public function preservesOrderOfKeptTools(): void
    {
        self::assertSame(
            ['actionlint', 'yamllint', 'shellcheck'],
            self::names(
                (new EnabledToolsFormula(['ci.infra_checks']))
                    ->op([], self::settings([
                        'ci.infra_checks' => new ListValue([
                            new StringValue('actionlint'),
                            new StringValue('hadolint'),
                            new StringValue('yamllint'),
                            new StringValue('shellcheck'),
                        ]),
                        'actionlint.cli' => new BoolValue(true),
                        'hadolint.cli' => new BoolValue(false),
                        'yamllint.cli' => new BoolValue(true),
                        'shellcheck.cli' => new BoolValue(true),
                    ])),
            ),
            'EnabledTools must preserve the original order of kept names',
        );
    }

    #[Test]
    public function throwsWhenCliFlagNotDeclared(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('setting "ghost.cli" not declared');

        (new EnabledToolsFormula(['ci.infra_checks']))
            ->op([], self::settings([
                'ci.infra_checks' => new ListValue([new StringValue('ghost')]),
            ]));
    }

    #[Test]
    public function throwsWhenAllToolsDisabled(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('resolved no enabled tools in "ci.infra_checks"');

        (new EnabledToolsFormula(['ci.infra_checks']))
            ->op([], self::settings([
                'ci.infra_checks' => new ListValue([new StringValue('actionlint')]),
                'actionlint.cli' => new BoolValue(false),
            ]));
    }

    #[Test]
    public function throwsWhenSettingsKeyAbsent(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('cannot find settings key "ci.missing"');

        (new EnabledToolsFormula(['ci.missing']))
            ->op([], self::settings([]));
    }

    #[Test]
    public function throwsWhenArgumentCountWrong(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('expects exactly one settings key, got 2');

        (new EnabledToolsFormula(['ci.a', 'ci.b']))
            ->op([], self::settings([]));
    }

    #[Test]
    public function throwsWhenSourceKeyIsNotAList(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('to be a list');

        (new EnabledToolsFormula(['ci.infra_checks']))
            ->op([], self::settings([
                'ci.infra_checks' => new BoolValue(true),
            ]));
    }

    #[Test]
    public function throwsWhenToolNameIsNotString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('expects string tool names');

        (new EnabledToolsFormula(['ci.infra_checks']))
            ->op([], self::settings([
                'ci.infra_checks' => new ListValue([new IntValue(42)]),
            ]));
    }

    #[Test]
    public function throwsWhenCliFlagIsNotBoolean(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('to be a boolean');

        (new EnabledToolsFormula(['ci.infra_checks']))
            ->op([], self::settings([
                'ci.infra_checks' => new ListValue([new StringValue('actionlint')]),
                'actionlint.cli' => new IntValue(1),
            ]));
    }

    /**
     * @return list<string>
     */
    private static function names(Op $op): array
    {
        if (!$op instanceof Listed) {
            throw new SheriffException('EnabledTools must produce a Listed pipeline source');
        }

        return array_map(
            static fn(Op $part): string => $part->rendered(),
            $op->parts(),
        );
    }

    /**
     * @param array<string, Value> $values
     */
    private static function settings(array $values): Settings
    {
        return new readonly class ($values) implements Settings {
            /**
             * @param array<string, Value> $values
             */
            public function __construct(private array $values) {}

            #[Override]
            public function has(string $name): bool
            {
                return array_key_exists($name, $this->values);
            }

            #[Override]
            public function value(string $name): Value
            {
                return $this->values[$name];
            }

            #[Override]
            public function keys(): array
            {
                return array_keys($this->values);
            }
        };
    }
}
