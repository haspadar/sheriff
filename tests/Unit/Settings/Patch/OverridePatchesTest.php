<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Settings\Patch\OverrideList;
use Haspadar\Sheriff\Settings\Patch\OverridePatches;
use Haspadar\Sheriff\Settings\Patch\OverrideScalar;
use Haspadar\Sheriff\Settings\Patch\OverrideTree;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OverridePatchesTest extends TestCase
{
    #[Test]
    public function returnsEmptyListForEmptySection(): void
    {
        self::assertSame(
            [],
            (new OverridePatches([]))->patches(),
            'OverridePatches must return no patches when the override section is empty',
        );
    }

    #[Test]
    public function buildsOverrideScalarForScalarValue(): void
    {
        $patches = (new OverridePatches(['phpstan.level' => 8]))->patches();

        self::assertInstanceOf(
            OverrideScalar::class,
            $patches[0],
            'OverridePatches must produce OverrideScalar for a scalar value',
        );
    }

    #[Test]
    public function buildsOverrideListForListValue(): void
    {
        $patches = (new OverridePatches(['phpstan.paths' => ['src', 'tests']]))->patches();

        self::assertInstanceOf(
            OverrideList::class,
            $patches[0],
            'OverridePatches must produce OverrideList for a list value',
        );
    }

    #[Test]
    public function buildsOverrideTreeForMappingValue(): void
    {
        $patches = (new OverridePatches([
            'phpstan.parameters' => ['haspadar' => ['ignoreAbstract' => true]],
        ]))->patches();

        self::assertInstanceOf(
            OverrideTree::class,
            $patches[0],
            'OverridePatches must produce OverrideTree for a mapping value',
        );
    }

    #[Test]
    public function preservesTargetKeyOnTheBuiltPatch(): void
    {
        $patches = (new OverridePatches(['phpstan.level' => 8]))->patches();

        self::assertSame(
            'phpstan.level',
            $patches[0]->key(),
            'OverridePatches must put the yaml key onto the produced patch',
        );
    }

    #[Test]
    public function buildsOverrideScalarForBooleanValue(): void
    {
        $patches = (new OverridePatches(['phpstan.cli' => true]))->patches();

        self::assertInstanceOf(
            OverrideScalar::class,
            $patches[0],
            'OverridePatches must produce OverrideScalar for a boolean value',
        );
    }

    #[Test]
    public function buildsOverrideScalarForFloatValue(): void
    {
        $patches = (new OverridePatches(['threshold' => 0.5]))->patches();

        self::assertInstanceOf(
            OverrideScalar::class,
            $patches[0],
            'OverridePatches must produce OverrideScalar for a float value',
        );
    }

    #[Test]
    public function buildsOverrideScalarForStringValue(): void
    {
        $patches = (new OverridePatches(['phpstan.memory' => '1G']))->patches();

        self::assertInstanceOf(
            OverrideScalar::class,
            $patches[0],
            'OverridePatches must produce OverrideScalar for a string value',
        );
    }

    #[Test]
    public function rejectsNullPayloadAsConfigError(): void
    {
        $this->expectException(SheriffException::class);

        (new OverridePatches(['phpstan.level' => null]))->patches();
    }
}
