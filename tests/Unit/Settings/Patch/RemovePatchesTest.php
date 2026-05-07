<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Settings\Patch;

use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Settings\Patch\RemoveList;
use Haspadar\Sheriff\Settings\Patch\RemovePatches;
use Haspadar\Sheriff\Settings\Patch\RemoveTree;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RemovePatchesTest extends TestCase
{
    #[Test]
    public function returnsEmptyListForEmptySection(): void
    {
        self::assertSame(
            [],
            (new RemovePatches([]))->patches(),
            'RemovePatches must return no patches when the remove section is empty',
        );
    }

    #[Test]
    public function buildsRemoveListForListValue(): void
    {
        $patches = (new RemovePatches(['phpstan.checked_exceptions' => ['\\Throwable']]))->patches();

        self::assertInstanceOf(
            RemoveList::class,
            $patches[0],
            'RemovePatches must produce RemoveList for a yaml list of items to drop',
        );
    }

    #[Test]
    public function preservesTargetKeyOnTheBuiltPatch(): void
    {
        $patches = (new RemovePatches(['phpstan.checked_exceptions' => ['\\Throwable']]))->patches();

        self::assertSame(
            'phpstan.checked_exceptions',
            $patches[0]->key(),
            'RemovePatches must put the yaml key onto the produced patch',
        );
    }

    #[Test]
    public function rejectsScalarPayloadAsConfigError(): void
    {
        $this->expectException(SheriffException::class);

        (new RemovePatches(['phpstan.level' => 8]))->patches();
    }

    #[Test]
    public function buildsRemoveTreeForMappingPayload(): void
    {
        $patches = (new RemovePatches([
            'phpstan.parameters' => [
                'haspadar' => [
                    'afferentCoupling' => [
                        'excludedClasses' => ['\\App\\Foo'],
                    ],
                ],
            ],
        ]))->patches();

        self::assertInstanceOf(
            RemoveTree::class,
            $patches[0],
            'RemovePatches must produce RemoveTree for a yaml mapping that walks into nested leaves',
        );
    }
}
