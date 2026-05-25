<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Xml;

use Haspadar\Sheriff\Chain\Render\Xml\PhpunitTestsuites;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhpunitTestsuitesTest extends TestCase
{
    #[Test]
    public function rendersOneSuitePerEntryWithSubdirsAppended(): void
    {
        self::assertSame(
            implode("\n", [
                '        <testsuite name="unit">',
                '            <directory suffix="Test.php">../../tests/Unit</directory>',
                '        </testsuite>',
                '        <testsuite name="integration">',
                '            <directory suffix="Test.php">../../tests/Integration</directory>',
                '        </testsuite>',
            ]),
            (new PhpunitTestsuites(
                new TreeValue([
                    'unit' => new ListValue([new StringValue('Unit')]),
                    'integration' => new ListValue([new StringValue('Integration')]),
                ]),
                new ListValue([new StringValue('tests')]),
            ))->rendered(),
            'PhpunitTestsuites must render one suite per tree entry with directories under php.tests',
        );
    }

    #[Test]
    public function expandsSubdirsAcrossMultipleBaseDirectories(): void
    {
        self::assertSame(
            implode("\n", [
                '        <testsuite name="unit">',
                '            <directory suffix="Test.php">../../tests/Unit</directory>',
                '            <directory suffix="Test.php">../../spec/Unit</directory>',
                '        </testsuite>',
            ]),
            (new PhpunitTestsuites(
                new TreeValue(['unit' => new ListValue([new StringValue('Unit')])]),
                new ListValue([new StringValue('tests'), new StringValue('spec')]),
            ))->rendered(),
            'PhpunitTestsuites must emit one directory per base/subdir pair',
        );
    }

    #[Test]
    public function emitsMultipleDirectoriesWhenSubdirsListHasManyEntries(): void
    {
        self::assertSame(
            implode("\n", [
                '        <testsuite name="feature">',
                '            <directory suffix="Test.php">../../tests/Feature</directory>',
                '            <directory suffix="Test.php">../../tests/Acceptance</directory>',
                '        </testsuite>',
            ]),
            (new PhpunitTestsuites(
                new TreeValue([
                    'feature' => new ListValue([
                        new StringValue('Feature'),
                        new StringValue('Acceptance'),
                    ]),
                ]),
                new ListValue([new StringValue('tests')]),
            ))->rendered(),
            'PhpunitTestsuites must list every subdir under the same suite block',
        );
    }

    #[Test]
    public function fallsBackToSingleDefaultSuiteWhenTreeIsEmpty(): void
    {
        self::assertSame(
            implode("\n", [
                '        <testsuite name="default">',
                '            <directory suffix="Test.php">../../tests</directory>',
                '        </testsuite>',
            ]),
            (new PhpunitTestsuites(
                new TreeValue([]),
                new ListValue([new StringValue('tests')]),
            ))->rendered(),
            'PhpunitTestsuites must render a single default suite over the base directories when the tree is empty',
        );
    }

    #[Test]
    public function acceptsEmptyListBaseAsEmptyTree(): void
    {
        self::assertSame(
            implode("\n", [
                '        <testsuite name="default">',
                '            <directory suffix="Test.php">../../tests</directory>',
                '        </testsuite>',
            ]),
            (new PhpunitTestsuites(
                new ListValue([]),
                new ListValue([new StringValue('tests')]),
            ))->rendered(),
            'PhpunitTestsuites must treat an empty ListValue as an empty tree because YAML `{}` parses as `[]`',
        );
    }

    #[Test]
    public function fallsBackToDefaultSuiteAcrossEveryBaseDirectory(): void
    {
        self::assertSame(
            implode("\n", [
                '        <testsuite name="default">',
                '            <directory suffix="Test.php">../../tests</directory>',
                '            <directory suffix="Test.php">../../spec</directory>',
                '        </testsuite>',
            ]),
            (new PhpunitTestsuites(
                new TreeValue([]),
                new ListValue([new StringValue('tests'), new StringValue('spec')]),
            ))->rendered(),
            'PhpunitTestsuites must include every base directory in the default suite',
        );
    }

    #[Test]
    public function throwsWhenBaseDirectoryListIsEmpty(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('requires at least one base directory');

        (new PhpunitTestsuites(
            new TreeValue(['unit' => new ListValue([new StringValue('Unit')])]),
            new ListValue([]),
        ))->rendered();
    }

    #[Test]
    public function throwsWhenSuitesPayloadIsNeitherTreeNorEmptyList(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('requires a TreeValue payload');

        (new PhpunitTestsuites(
            new StringValue('oops'),
            new ListValue([new StringValue('tests')]),
        ))->rendered();
    }

    #[Test]
    public function throwsWhenBaseDirectoriesAreNotAList(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('expects base directories to be a list');

        (new PhpunitTestsuites(
            new TreeValue([]),
            new StringValue('tests'),
        ))->rendered();
    }

    #[Test]
    public function throwsWhenBaseDirectoryEntryIsNotAString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('base directories to contain strings');

        (new PhpunitTestsuites(
            new TreeValue(['unit' => new ListValue([new StringValue('Unit')])]),
            new ListValue([new IntValue(42)]),
        ))->rendered();
    }

    #[Test]
    public function throwsWhenSuiteSubdirsAreNotAList(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('suite "unit" to be a list');

        (new PhpunitTestsuites(
            new TreeValue(['unit' => new StringValue('Unit')]),
            new ListValue([new StringValue('tests')]),
        ))->rendered();
    }

    #[Test]
    public function throwsWhenSuiteSubdirEntryIsNotAString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessage('suite "unit" to contain strings');

        (new PhpunitTestsuites(
            new TreeValue(['unit' => new ListValue([new IntValue(42)])]),
            new ListValue([new StringValue('tests')]),
        ))->rendered();
    }
}
