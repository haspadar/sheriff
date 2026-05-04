<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Settings\Patch\AppendList;
use Haspadar\Sheriff\Settings\Patch\AppendPatches;
use Haspadar\Sheriff\Settings\Patch\AppendTree;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AppendPatchesTest extends TestCase
{
    #[Test]
    public function returnsEmptyListForEmptySection(): void
    {
        self::assertSame(
            [],
            (new AppendPatches([]))->patches(),
            'AppendPatches must return no patches when the append section is empty',
        );
    }

    #[Test]
    public function buildsAppendListForListValue(): void
    {
        $patches = (new AppendPatches(['infra.exclude' => ['dist']]))->patches();

        self::assertInstanceOf(
            AppendList::class,
            $patches[0],
            'AppendPatches must produce AppendList for a list value',
        );
    }

    #[Test]
    public function buildsAppendTreeForMappingValue(): void
    {
        $patches = (new AppendPatches([
            'phpstan.parameters' => ['ignoreErrors' => ['#new#']],
        ]))->patches();

        self::assertInstanceOf(
            AppendTree::class,
            $patches[0],
            'AppendPatches must produce AppendTree for a mapping value',
        );
    }

    #[Test]
    public function preservesTargetKeyOnTheBuiltPatch(): void
    {
        $patches = (new AppendPatches(['infra.exclude' => ['dist']]))->patches();

        self::assertSame(
            'infra.exclude',
            $patches[0]->key(),
            'AppendPatches must put the yaml key onto the produced patch',
        );
    }

    #[Test]
    public function preservesTargetKeyOnTreePatch(): void
    {
        $patches = (new AppendPatches([
            'phpstan.parameters' => ['ignoreErrors' => ['#new#']],
        ]))->patches();

        self::assertSame(
            'phpstan.parameters',
            $patches[0]->key(),
            'AppendPatches must put the yaml key onto the produced tree patch',
        );
    }

    #[Test]
    public function rejectsScalarPayloadAsConfigError(): void
    {
        $this->expectException(SheriffException::class);

        (new AppendPatches(['phpstan.level' => 8]))->patches();
    }
}
