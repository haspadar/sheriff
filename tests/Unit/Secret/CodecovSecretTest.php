<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Secret;

use Haspadar\Sheriff\Secret\CodecovSecret;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CodecovSecretTest extends TestCase
{
    #[Test]
    public function enabledWhenPhpUnitEnabled(): void
    {
        self::assertTrue(
            (new CodecovSecret())->enabled(new FakeSettings(['phpunit.cli' => new BoolValue(true)])),
            'CodecovSecret must be enabled when phpunit.cli is true',
        );
    }

    #[Test]
    public function enabledWhenKeyAbsent(): void
    {
        self::assertTrue(
            (new CodecovSecret())->enabled(new FakeSettings([])),
            'CodecovSecret must default to enabled when phpunit.cli key is absent',
        );
    }

    #[Test]
    public function disabledWhenPhpUnitDisabled(): void
    {
        self::assertFalse(
            (new CodecovSecret())->enabled(new FakeSettings(['phpunit.cli' => new BoolValue(false)])),
            'CodecovSecret must be disabled when phpunit.cli is false',
        );
    }

    #[Test]
    public function returnsCorrectName(): void
    {
        self::assertSame(
            'CODECOV_TOKEN',
            (new CodecovSecret())->name(),
            'CodecovSecret name must be CODECOV_TOKEN',
        );
    }

    #[Test]
    public function returnsUrlWithOrg(): void
    {
        self::assertSame(
            'https://app.codecov.io/account/gh/acme/repositories',
            (new CodecovSecret())->url('acme'),
            'CodecovSecret url must include the org name',
        );
    }
}
