<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Parse;

use Haspadar\Sheriff\Chain\Parse\PhpunitTestsuitesFormula;
use Haspadar\Sheriff\Chain\Render\Xml\PhpunitTestsuites;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhpunitTestsuitesFormulaTest extends TestCase
{
    #[Test]
    public function buildsPhpunitTestsuitesOpFromTwoSettingsKeys(): void
    {
        self::assertInstanceOf(
            PhpunitTestsuites::class,
            (new PhpunitTestsuitesFormula(['phpunit.testsuites', 'php.tests']))
                ->op([], new FakeSettings([
                    'phpunit.testsuites' => new TreeValue([
                        'unit' => new ListValue([new StringValue('Unit')]),
                    ]),
                    'php.tests' => new ListValue([new StringValue('tests')]),
                ])),
            'PhpunitTestsuitesFormula must produce a PhpunitTestsuites op from two resolved keys',
        );
    }

    #[Test]
    public function rendersConfiguredSuitesWhenBothKeysResolveToData(): void
    {
        self::assertSame(
            implode("\n", [
                '        <testsuite name="unit">',
                '            <directory suffix="Test.php">../../tests/Unit</directory>',
                '        </testsuite>',
            ]),
            (new PhpunitTestsuitesFormula(['phpunit.testsuites', 'php.tests']))
                ->op([], new FakeSettings([
                    'phpunit.testsuites' => new TreeValue([
                        'unit' => new ListValue([new StringValue('Unit')]),
                    ]),
                    'php.tests' => new ListValue([new StringValue('tests')]),
                ]))
                ->rendered(),
            'PhpunitTestsuitesFormula must wire the resolved tree and base dirs into PhpunitTestsuites',
        );
    }

    #[Test]
    public function throwsWhenArgumentCountWrong(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('got 1 arguments');

        (new PhpunitTestsuitesFormula(['phpunit.testsuites']))
            ->op([], new FakeSettings([]));
    }

    #[Test]
    public function throwsWhenTestsuitesKeyAbsent(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('cannot find settings key "phpunit.testsuites"');

        (new PhpunitTestsuitesFormula(['phpunit.testsuites', 'php.tests']))
            ->op([], new FakeSettings([
                'php.tests' => new ListValue([new StringValue('tests')]),
            ]));
    }

    #[Test]
    public function throwsWhenBaseDirsKeyAbsent(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('cannot find settings key "php.tests"');

        (new PhpunitTestsuitesFormula(['phpunit.testsuites', 'php.tests']))
            ->op([], new FakeSettings([
                'phpunit.testsuites' => new TreeValue([]),
            ]));
    }
}
