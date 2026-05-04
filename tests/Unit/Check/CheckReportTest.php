<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\CheckReport;
use Haspadar\Sheriff\Tests\Fake\Output\FakeOutput;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckReportTest extends TestCase
{
    #[Test]
    public function formatsStartedMessageWithCounterInBatch(): void
    {
        $output = new FakeOutput();

        (new CheckReport($output, 3))->started('phpstan', 2);

        self::assertSame(
            '[RUN]  phpstan               2/3',
            $output->muteds()[0],
            'started() must format [RUN] with padded name and counter in batch',
        );
    }

    #[Test]
    public function formatsStartedMessageWithoutCounterWhenSingle(): void
    {
        $output = new FakeOutput();

        (new CheckReport($output, 1))->started('phpstan', 1);

        self::assertSame(
            '[RUN]  phpstan',
            $output->muteds()[0],
            'started() must format [RUN] with name only when single check',
        );
    }

    #[Test]
    public function formatsPassedMessageWithElapsedTime(): void
    {
        $output = new FakeOutput();

        (new CheckReport($output, 1))->passed('phpstan', 2.0);

        self::assertSame(
            '[OK]   phpstan              2.0s',
            $output->successes()[0],
            'passed() must format [OK] with padded name and elapsed time',
        );
    }

    #[Test]
    public function formatsFailedMessageWithElapsedTime(): void
    {
        $output = new FakeOutput();

        (new CheckReport($output, 1))->failed('phpstan', 3.0);

        self::assertSame(
            '[FAIL] phpstan              3.0s',
            $output->errors()[0],
            'failed() must format [FAIL] with padded name and elapsed time',
        );
    }

    #[Test]
    public function writesSuccessOutputChannelWhenPassed(): void
    {
        $output = new FakeOutput();

        (new CheckReport($output, 1))->passed('phpstan', 1.5);

        self::assertCount(
            1,
            $output->successes(),
            'passed() must write exactly one success message',
        );
    }

    #[Test]
    public function writesErrorOutputChannelWhenFailed(): void
    {
        $output = new FakeOutput();

        (new CheckReport($output, 1))->failed('phpstan', 0.5);

        self::assertCount(
            1,
            $output->errors(),
            'failed() must write exactly one error message',
        );
    }

    #[Test]
    public function writesMutedOutputChannelWhenStarted(): void
    {
        $output = new FakeOutput();

        (new CheckReport($output, 3))->started('phpstan', 1);

        self::assertCount(
            1,
            $output->muteds(),
            'started() must write exactly one muted message',
        );
    }
}
