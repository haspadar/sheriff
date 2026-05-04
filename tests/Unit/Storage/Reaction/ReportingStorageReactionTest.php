<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Storage\Reaction;

use Haspadar\Sheriff\Storage\Reaction\ReportingStorageReaction;
use Haspadar\Sheriff\Tests\Fake\Output\FakeOutput;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReportingStorageReactionTest extends TestCase
{
    #[Test]
    public function writesSuccessMessageWhenFileIsCreated(): void
    {
        $output = new FakeOutput();

        (new ReportingStorageReaction($output))
            ->created('file.txt');

        self::assertCount(
            1,
            $output->successes(),
            'ReportingStorageReaction must write one success message when a file is created',
        );
    }

    #[Test]
    public function writesInfoMessageWhenFileIsUpdated(): void
    {
        $output = new FakeOutput();

        (new ReportingStorageReaction($output))
            ->updated('file.txt');

        self::assertCount(
            1,
            $output->infos(),
            'ReportingStorageReaction must write one info message when a file is updated',
        );
    }

    #[Test]
    public function writesMutedMessageWhenFileIsSkipped(): void
    {
        $output = new FakeOutput();

        (new ReportingStorageReaction($output))
            ->skipped('file.txt');

        self::assertCount(
            1,
            $output->muteds(),
            'ReportingStorageReaction must write one muted message when a file is skipped',
        );
    }
}
