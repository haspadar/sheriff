<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings;

use Haspadar\Sheriff\Settings\ComposerSettings;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ComposerSettingsTest extends TestCase
{
    #[Test]
    public function exposesRootNamespaceKeyDerivedFromComposerJson(): void
    {
        $folder = (new TempFolder())->withFile(
            'composer.json',
            json_encode(['autoload' => ['psr-4' => ['App\\' => 'src/']]], JSON_THROW_ON_ERROR),
        );

        $value = (new ComposerSettings(
            new FakeSettings([]),
            $folder->path() . '/composer.json',
        ))->value('phpcs.root_namespace');

        $folder->close();

        self::assertEquals(
            new StringValue('App'),
            $value,
            'ComposerSettings must read the first PSR-4 namespace from composer.json',
        );
    }

    #[Test]
    public function rendersEmptyStringWhenComposerJsonIsAbsent(): void
    {
        self::assertEquals(
            new StringValue(''),
            (new ComposerSettings(
                new FakeSettings([]),
                '/nonexistent/path/composer.json',
            ))->value('phpcs.root_namespace'),
            'ComposerSettings must return an empty StringValue when composer.json is missing',
        );
    }

    #[Test]
    public function reportsRootNamespaceKeyAsAvailableEvenWithoutBase(): void
    {
        self::assertTrue(
            (new ComposerSettings(
                new FakeSettings([]),
                '/anywhere/composer.json',
            ))->has('phpcs.root_namespace'),
            'ComposerSettings must report phpcs.root_namespace as available regardless of the base settings',
        );
    }

    #[Test]
    public function exposesTestsRootNamespaceKeyDerivedFromComposerJson(): void
    {
        $folder = (new TempFolder())->withFile(
            'composer.json',
            json_encode(
                [
                    'autoload' => ['psr-4' => ['App\\' => 'src/']],
                    'autoload-dev' => ['psr-4' => ['App\\Tests\\' => 'tests/']],
                ],
                JSON_THROW_ON_ERROR,
            ),
        );

        $value = (new ComposerSettings(
            new FakeSettings([]),
            $folder->path() . '/composer.json',
        ))->value('phpcs.tests_root_namespace');

        $folder->close();

        self::assertEquals(
            new StringValue('App\\Tests'),
            $value,
            'ComposerSettings must read the first PSR-4 namespace from autoload-dev in composer.json',
        );
    }

    #[Test]
    public function rendersEmptyTestsRootNamespaceWhenComposerJsonIsAbsent(): void
    {
        self::assertEquals(
            new StringValue(''),
            (new ComposerSettings(
                new FakeSettings([]),
                '/nonexistent/path/composer.json',
            ))->value('phpcs.tests_root_namespace'),
            'ComposerSettings must return an empty StringValue for tests root namespace when composer.json is missing',
        );
    }

    #[Test]
    public function reportsTestsRootNamespaceKeyAsAvailableEvenWithoutBase(): void
    {
        self::assertTrue(
            (new ComposerSettings(
                new FakeSettings([]),
                '/anywhere/composer.json',
            ))->has('phpcs.tests_root_namespace'),
            'ComposerSettings must report phpcs.tests_root_namespace as available regardless of the base settings',
        );
    }

    #[Test]
    public function letsBaseSettingsOverrideTheDerivedTestsNamespace(): void
    {
        self::assertEquals(
            new StringValue('Custom\\Tests'),
            (new ComposerSettings(
                new FakeSettings(['phpcs.tests_root_namespace' => new StringValue('Custom\\Tests')]),
                '/anywhere/composer.json',
            ))->value('phpcs.tests_root_namespace'),
            'User-provided phpcs.tests_root_namespace must win over the composer.json derivation',
        );
    }

    #[Test]
    public function delegatesUnknownKeysToBaseSettings(): void
    {
        self::assertEquals(
            new IntValue(9),
            (new ComposerSettings(
                new FakeSettings(['phpstan.level' => new IntValue(9)]),
                '/anywhere/composer.json',
            ))->value('phpstan.level'),
            'ComposerSettings must pass through non-namespace keys to the base settings',
        );
    }

    #[Test]
    public function letsBaseSettingsOverrideTheDerivedNamespace(): void
    {
        self::assertEquals(
            new StringValue('Custom'),
            (new ComposerSettings(
                new FakeSettings(['phpcs.root_namespace' => new StringValue('Custom')]),
                '/anywhere/composer.json',
            ))->value('phpcs.root_namespace'),
            'User-provided phpcs.root_namespace must win over the composer.json derivation',
        );
    }

    #[Test]
    public function delegatesHasToBaseSettingsForOtherKeys(): void
    {
        self::assertFalse(
            (new ComposerSettings(
                new FakeSettings([]),
                '/anywhere/composer.json',
            ))->has('phpstan.level'),
            'ComposerSettings must delegate has() to the base settings for non-namespace keys',
        );
    }

    #[Test]
    public function exposesBothDerivedNamespaceKeysOnTopOfBaseKeys(): void
    {
        self::assertSame(
            ['phpstan.level', 'phpcs.root_namespace', 'phpcs.tests_root_namespace'],
            (new ComposerSettings(
                new FakeSettings(['phpstan.level' => new IntValue(9)]),
                '/anywhere/composer.json',
            ))->keys(),
            'ComposerSettings must append both derived namespace keys to the base keys',
        );
    }
}
