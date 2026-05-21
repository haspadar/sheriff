<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Parse;

use Haspadar\Sheriff\Chain\Listed;
use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Chain\Parse\JoinedListsFormula;
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

final class JoinedListsFormulaTest extends TestCase
{
    #[Test]
    public function joinsSingleLeftWithSingleRight(): void
    {
        self::assertSame(
            ['tests/Unit'],
            self::strings(
                (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
                    ->op([], self::settings([
                        'php.tests' => new ListValue([new StringValue('tests')]),
                        'phpunit.testsuites.unit' => new ListValue([new StringValue('Unit')]),
                    ])),
            ),
            'JoinedLists must join one-to-one pairs with the separator',
        );
    }

    #[Test]
    public function emitsCartesianProductOfBothLists(): void
    {
        self::assertSame(
            ['tests/Unit', 'tests/Integration', 'spec/Unit', 'spec/Integration'],
            self::strings(
                (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
                    ->op([], self::settings([
                        'php.tests' => new ListValue([
                            new StringValue('tests'),
                            new StringValue('spec'),
                        ]),
                        'phpunit.testsuites.unit' => new ListValue([
                            new StringValue('Unit'),
                            new StringValue('Integration'),
                        ]),
                    ])),
            ),
            'JoinedLists must emit a left-major cartesian product',
        );
    }

    #[Test]
    public function returnsEmptyListWhenLeftIsEmpty(): void
    {
        self::assertSame(
            [],
            self::strings(
                (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
                    ->op([], self::settings([
                        'php.tests' => new ListValue([]),
                        'phpunit.testsuites.unit' => new ListValue([new StringValue('Unit')]),
                    ])),
            ),
            'JoinedLists must return an empty list when the left key resolves to no values',
        );
    }

    #[Test]
    public function returnsEmptyListWhenRightIsEmpty(): void
    {
        self::assertSame(
            [],
            self::strings(
                (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
                    ->op([], self::settings([
                        'php.tests' => new ListValue([new StringValue('tests')]),
                        'phpunit.testsuites.unit' => new ListValue([]),
                    ])),
            ),
            'JoinedLists must return an empty list when the right key resolves to no values',
        );
    }

    #[Test]
    public function throwsWhenArgumentCountWrong(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('two settings keys and a separator, got 2');

        (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit']))
            ->op([], self::settings([]));
    }

    #[Test]
    public function throwsWhenLeftKeyAbsent(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('cannot find settings key "php.tests"');

        (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
            ->op([], self::settings([
                'phpunit.testsuites.unit' => new ListValue([new StringValue('Unit')]),
            ]));
    }

    #[Test]
    public function throwsWhenRightKeyAbsent(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('cannot find settings key "phpunit.testsuites.unit"');

        (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
            ->op([], self::settings([
                'php.tests' => new ListValue([new StringValue('tests')]),
            ]));
    }

    #[Test]
    public function throwsWhenLeftKeyIsNotList(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('"php.tests" to be a list');

        (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
            ->op([], self::settings([
                'php.tests' => new BoolValue(true),
                'phpunit.testsuites.unit' => new ListValue([new StringValue('Unit')]),
            ]));
    }

    #[Test]
    public function throwsWhenLeftElementIsNotString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('"php.tests" to contain strings');

        (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
            ->op([], self::settings([
                'php.tests' => new ListValue([new IntValue(42)]),
                'phpunit.testsuites.unit' => new ListValue([new StringValue('Unit')]),
            ]));
    }

    #[Test]
    public function throwsWhenRightKeyIsNotList(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('"phpunit.testsuites.unit" to be a list');

        (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
            ->op([], self::settings([
                'php.tests' => new ListValue([new StringValue('tests')]),
                'phpunit.testsuites.unit' => new BoolValue(true),
            ]));
    }

    #[Test]
    public function throwsWhenRightElementIsNotString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('"phpunit.testsuites.unit" to contain strings');

        (new JoinedListsFormula(['php.tests', 'phpunit.testsuites.unit', '/']))
            ->op([], self::settings([
                'php.tests' => new ListValue([new StringValue('tests')]),
                'phpunit.testsuites.unit' => new ListValue([new IntValue(42)]),
            ]));
    }

    /**
     * @return list<string>
     */
    private static function strings(Op $op): array
    {
        if (!$op instanceof Listed) {
            throw new SheriffException('JoinedLists must produce a Listed pipeline source');
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
