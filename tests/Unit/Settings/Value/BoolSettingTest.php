<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Value;

use Haspadar\Sheriff\Settings\Value\BoolSetting;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BoolSettingTest extends TestCase
{
    #[Test]
    public function readsBoolValueWhenKeyPresent(): void
    {
        self::assertFalse(
            (new BoolSetting(
                new FakeSettings(['flag' => new BoolValue(false)]),
                'flag',
                true,
            ))->raw(),
            'BoolSetting must unwrap a BoolValue payload regardless of the default',
        );
    }

    #[Test]
    public function fallsBackToDefaultWhenKeyAbsent(): void
    {
        self::assertTrue(
            (new BoolSetting(
                new FakeSettings([]),
                'flag',
                true,
            ))->raw(),
            'BoolSetting must return the default when the key is missing',
        );
    }

    #[Test]
    public function fallsBackToDefaultWhenValueIsNotBoolValue(): void
    {
        self::assertTrue(
            (new BoolSetting(
                new FakeSettings(['flag' => new StringValue('false')]),
                'flag',
                true,
            ))->raw(),
            'BoolSetting must return the default when the stored value is not a BoolValue',
        );
    }
}
