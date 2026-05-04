<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config;

use Haspadar\Sheriff\Config\AppendConfig;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Fake\Config\FakeConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AppendConfigTest extends TestCase
{
    #[Test]
    public function returnsTrueWhenKeyIsDeclaredInDefaults(): void
    {
        self::assertTrue(
            (new AppendConfig(
                new FakeConfig(['phpstan.neon_includes' => []]),
                [],
            ))->has('phpstan.neon_includes'),
            'AppendConfig must report a key as present if it exists in defaults',
        );
    }

    #[Test]
    public function returnsFalseWhenKeyIsNotDeclaredInDefaults(): void
    {
        self::assertFalse(
            (new AppendConfig(
                new FakeConfig(['phpstan.neon_includes' => []]),
                [],
            ))->has('unknown.key'),
            'AppendConfig must report an undeclared key as absent',
        );
    }

    #[Test]
    public function returnsDefaultListWhenNoAppendsForKey(): void
    {
        self::assertSame(
            ['../../rules.neon'],
            (new AppendConfig(
                new FakeConfig(['phpstan.neon_includes' => ['../../rules.neon']]),
                [],
            ))->list('phpstan.neon_includes'),
            'AppendConfig must return the default list unchanged when no appends are defined for the key',
        );
    }

    #[Test]
    public function appendsValuesToDefaultList(): void
    {
        self::assertSame(
            ['../../rules.neon', '../../extra.neon'],
            (new AppendConfig(
                new FakeConfig(['phpstan.neon_includes' => ['../../rules.neon']]),
                ['phpstan.neon_includes' => ['../../extra.neon']],
            ))->list('phpstan.neon_includes'),
            'AppendConfig must merge appended values after the default list',
        );
    }

    #[Test]
    public function appendsMultipleValuesToEmptyDefaultList(): void
    {
        self::assertSame(
            ['legacy', 'generated'],
            (new AppendConfig(
                new FakeConfig(['exclude' => []]),
                ['exclude' => ['legacy', 'generated']],
            ))->list('exclude'),
            'AppendConfig must append to an empty default list',
        );
    }

    #[Test]
    public function throwsWhenListCalledForUndeclaredKey(): void
    {
        $this->expectException(SheriffException::class);

        (new AppendConfig(
            new FakeConfig([]),
            [],
        ))->list('phpstan.neon_includes');
    }

    #[Test]
    public function toArrayReturnsAllKeysWithAppendsApplied(): void
    {
        self::assertSame(
            [
                'phpstan.neon_includes' => ['../../rules.neon', '../../extra.neon'],
                'exclude' => ['vendor', 'tests'],
            ],
            (new AppendConfig(
                new FakeConfig([
                    'phpstan.neon_includes' => ['../../rules.neon'],
                    'exclude' => ['vendor', 'tests'],
                ]),
                ['phpstan.neon_includes' => ['../../extra.neon']],
            ))->toArray(),
            'toArray must return all keys with appended values merged in',
        );
    }

    #[Test]
    public function throwsWhenAppendValueIsAssociativeArray(): void
    {
        $this->expectException(SheriffException::class);

        (new AppendConfig(
            new FakeConfig(['phpstan.neon_includes' => []]),
            ['phpstan.neon_includes' => ['key' => 'value']],
        ))->list('phpstan.neon_includes');
    }

    #[Test]
    public function throwsWhenAppendValueIsScalar(): void
    {
        $this->expectException(SheriffException::class);

        (new AppendConfig(
            new FakeConfig(['phpstan.neon_includes' => []]),
            ['phpstan.neon_includes' => '../../rules.neon'],
        ))->list('phpstan.neon_includes');
    }

    #[Test]
    public function throwsWhenAppendListContainsNonScalar(): void
    {
        $this->expectException(SheriffException::class);

        (new AppendConfig(
            new FakeConfig(['phpstan.neon_includes' => []]),
            ['phpstan.neon_includes' => [['nested']]],
        ))->list('phpstan.neon_includes');
    }

    #[Test]
    public function toArrayReturnsAllKeysUnchangedWhenNoAppends(): void
    {
        self::assertSame(
            ['phpstan.level' => ['8'], 'phpstan.paths' => ['src']],
            (new AppendConfig(
                new FakeConfig(['phpstan.level' => ['8'], 'phpstan.paths' => ['src']]),
                [],
            ))->toArray(),
            'toArray must return all keys unchanged when no appends are defined',
        );
    }
}
