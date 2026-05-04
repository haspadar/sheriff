<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings;

use Haspadar\Sheriff\Settings\Patch\AppendList;
use Haspadar\Sheriff\Settings\Patch\OverrideScalar;
use Haspadar\Sheriff\Settings\Patch\RemoveList;
use Haspadar\Sheriff\Settings\YamlPatches;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class YamlPatchesTest extends TestCase
{
    #[Test]
    public function returnsEmptyListWhenAllSectionsAreAbsent(): void
    {
        $folder = (new TempFolder())->withFile('.sheriff.yaml', "other: 1\n");

        self::assertSame(
            [],
            (new YamlPatches($folder->path() . '/.sheriff.yaml'))->patches(),
            'YamlPatches must return no patches when override/append/remove sections are missing',
        );
    }

    #[Test]
    public function buildsOverrideScalarFromOverrideSection(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "override:\n  phpstan.level: 8\n",
        );

        $patches = (new YamlPatches($folder->path() . '/.sheriff.yaml'))->patches();

        self::assertInstanceOf(
            OverrideScalar::class,
            $patches[0],
            'YamlPatches must read override scalars and produce OverrideScalar',
        );
    }

    #[Test]
    public function buildsAppendListFromAppendSection(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "append:\n  infra.exclude:\n    - dist\n",
        );

        $patches = (new YamlPatches($folder->path() . '/.sheriff.yaml'))->patches();

        self::assertInstanceOf(
            AppendList::class,
            $patches[0],
            'YamlPatches must read append lists and produce AppendList',
        );
    }

    #[Test]
    public function buildsRemoveListFromRemoveSection(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "remove:\n  phpstan.checked_exceptions:\n    - '\\Throwable'\n",
        );

        $patches = (new YamlPatches($folder->path() . '/.sheriff.yaml'))->patches();

        self::assertInstanceOf(
            RemoveList::class,
            $patches[0],
            'YamlPatches must read remove lists and produce RemoveList',
        );
    }

    #[Test]
    public function combinesPatchesFromAllThreeSectionsIntoSingleList(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "override:\n  phpstan.level: 8\nappend:\n  infra.exclude:\n    - dist\nremove:\n  phpstan.checked_exceptions:\n    - '\\Throwable'\n",
        );

        $patches = (new YamlPatches($folder->path() . '/.sheriff.yaml'))->patches();

        self::assertCount(
            3,
            $patches,
            'YamlPatches must combine override, append and remove patches into a single list',
        );
    }

    #[Test]
    public function ordersPatchesAsOverrideAppendRemoveRegardlessOfYamlDeclaration(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            "remove:\n  phpstan.checked_exceptions:\n    - '\\Throwable'\nappend:\n  infra.exclude:\n    - dist\noverride:\n  phpstan.level: 8\n",
        );

        $patches = (new YamlPatches($folder->path() . '/.sheriff.yaml'))->patches();

        self::assertInstanceOf(
            OverrideScalar::class,
            $patches[0],
            'YamlPatches must place override patches first regardless of yaml section order',
        );
    }
}
