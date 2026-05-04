<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Config;

use Haspadar\Sheriff\Config\DefaultConfig;
use Haspadar\Sheriff\Config\YamlPathKeys;
use Haspadar\Sheriff\SheriffException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class YamlPathKeysTest extends TestCase
{
    #[Test]
    public function throwsWhenOverridePhpSrcContainsNonString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessageMatches('/override\.php\.src/');

        $keys = new YamlPathKeys(['php.src' => [42]], [], new DefaultConfig());
        $keys->phpSrc();
    }

    #[Test]
    public function throwsWhenOverrideInfraExcludeContainsNonString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessageMatches('/override\.infra\.exclude/');

        $keys = new YamlPathKeys(['infra.exclude' => [true]], [], new DefaultConfig());
        $keys->infraExclude();
    }

    #[Test]
    public function throwsWhenAppendInfraExcludeContainsNonString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessageMatches('/append\.infra\.exclude/');

        $keys = new YamlPathKeys([], ['infra.exclude' => [null]], new DefaultConfig());
        $keys->infraExclude();
    }

    #[Test]
    public function throwsWhenAppendPhpSrcContainsNonString(): void
    {
        $this->expectException(SheriffException::class);
        $this->expectExceptionMessageMatches('/append\.php\.src/');

        $keys = new YamlPathKeys([], ['php.src' => [3.14]], new DefaultConfig());
        $keys->phpSrc();
    }

    #[Test]
    public function deduplicatesAppendedPhpSrcEntries(): void
    {
        $keys = new YamlPathKeys(
            ['php.src' => ['src']],
            ['php.src' => ['src', 'lib']],
            new DefaultConfig(),
        );

        self::assertSame(
            ['src', 'lib'],
            $keys->phpSrc(),
            'Appended php.src entries must be deduplicated',
        );
    }

    #[Test]
    public function deduplicatesAppendedInfraExcludeEntries(): void
    {
        $keys = new YamlPathKeys(
            ['infra.exclude' => ['.git']],
            ['infra.exclude' => ['.git', 'dist']],
            new DefaultConfig(),
        );

        self::assertSame(
            ['.git', 'dist'],
            $keys->infraExclude(),
            'Appended infra.exclude entries must be deduplicated',
        );
    }

    #[Test]
    public function normalizesAssociativeOverridePhpSrcKeys(): void
    {
        $keys = new YamlPathKeys(
            ['php.src' => ['a' => 'src', 'b' => 'lib']],
            [],
            new DefaultConfig(),
        );

        self::assertSame(
            ['src', 'lib'],
            $keys->phpSrc(),
            'Associative php.src overrides must be normalized to a sequential list',
        );
    }

    #[Test]
    public function normalizesAssociativeOverrideInfraExcludeKeys(): void
    {
        $keys = new YamlPathKeys(
            ['infra.exclude' => ['x' => '.git', 'y' => 'dist']],
            [],
            new DefaultConfig(),
        );

        self::assertSame(
            ['.git', 'dist'],
            $keys->infraExclude(),
            'Associative infra.exclude overrides must be normalized to a sequential list',
        );
    }

    #[Test]
    public function normalizesAssociativeAppendPhpSrcKeys(): void
    {
        $keys = new YamlPathKeys(
            ['php.src' => ['src']],
            ['php.src' => ['a' => 'lib']],
            new DefaultConfig(),
        );

        self::assertSame(
            ['src', 'lib'],
            $keys->phpSrc(),
            'Associative append php.src must be normalized before merging',
        );
    }

    #[Test]
    public function normalizesAssociativeAppendInfraExcludeKeys(): void
    {
        $keys = new YamlPathKeys(
            ['infra.exclude' => ['.git']],
            ['infra.exclude' => ['a' => 'dist']],
            new DefaultConfig(),
        );

        self::assertSame(
            ['.git', 'dist'],
            $keys->infraExclude(),
            'Associative append infra.exclude must be normalized before merging',
        );
    }
}
