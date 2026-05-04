<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings;

use Haspadar\Sheriff\Settings\PatchedSettings;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Tests\Fake\Settings\EchoPatch;
use Haspadar\Sheriff\Tests\Fake\Settings\FakePatch;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PatchedSettingsTest extends TestCase
{
    #[Test]
    public function returnsPatchedValueWhenKeyMatches(): void
    {
        $settings = new PatchedSettings(
            new FakeSettings(['phpstan.level' => new IntValue(9)]),
            new FakePatch('phpstan.level', new IntValue(7)),
        );

        self::assertEquals(
            new IntValue(7),
            $settings->value('phpstan.level'),
            'PatchedSettings must return the patched value for the targeted key',
        );
    }

    #[Test]
    public function returnsBaseValueWhenKeyDoesNotMatch(): void
    {
        $settings = new PatchedSettings(
            new FakeSettings(['phpstan.level' => new IntValue(9)]),
            new FakePatch('other.key', new IntValue(7)),
        );

        self::assertEquals(
            new IntValue(9),
            $settings->value('phpstan.level'),
            'PatchedSettings must return the base value for keys outside the patch scope',
        );
    }

    #[Test]
    public function passesBaseValueToPatch(): void
    {
        $settings = new PatchedSettings(
            new FakeSettings(['phpstan.level' => new IntValue(9)]),
            new EchoPatch('phpstan.level'),
        );

        self::assertEquals(
            new IntValue(9),
            $settings->value('phpstan.level'),
            'PatchedSettings must pass the base value to the patch and return the patch result',
        );
    }

    #[Test]
    public function delegatesHasToBaseSettings(): void
    {
        $settings = new PatchedSettings(
            new FakeSettings(['phpstan.level' => new IntValue(9)]),
            new FakePatch('phpstan.level', new IntValue(7)),
        );

        self::assertTrue(
            $settings->has('phpstan.level'),
            'PatchedSettings must delegate has() to the base settings',
        );
    }

    #[Test]
    public function ignoresPatchKeyWhenCheckingHas(): void
    {
        $settings = new PatchedSettings(
            new FakeSettings(['phpstan.level' => new IntValue(9)]),
            new FakePatch('absent.key', new IntValue(7)),
        );

        self::assertFalse(
            $settings->has('absent.key'),
            'PatchedSettings must not report has() true for a key only declared by the patch',
        );
    }
}
