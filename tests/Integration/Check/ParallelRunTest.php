<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Integration\Check;

use Haspadar\Piqule\Check\ParallelRun;
use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Tests\Fake\Check\FakeCheck;
use Haspadar\Piqule\Tests\Fake\Check\FakeChecks;
use Haspadar\Piqule\Tests\Fake\Check\FakeCliOption;
use Haspadar\Piqule\Tests\Fake\Output\FakeOutput;
use Haspadar\Piqule\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParallelRunTest extends TestCase
{
    #[Test]
    public function reportsPositionsWhenIndependentChecksPass(): void
    {
        $output = new FakeOutput();

        (new ParallelRun(
            new FakeChecks([
                new FakeCheck('alpha', '/dev/null'),
                new FakeCheck('bravo', '/dev/null'),
            ]),
            $output,
            new FakeCliOption(false),
        ))->run();

        self::assertSame(
            [
                sprintf('[RUN]  %-20s%5s', 'alpha', '1/2'),
                sprintf('[RUN]  %-20s%5s', 'bravo', '2/2'),
            ],
            $output->muteds(),
            'parallel run must report independent checks with sequential positions',
        );
    }

    #[Test]
    public function runsDependentChecksAfterIndependentChecks(): void
    {
        $output = new FakeOutput();

        (new ParallelRun(
            new FakeChecks([
                new FakeCheck('sonar', '/dev/null'),
                new FakeCheck('phpunit', '/dev/null'),
            ]),
            $output,
            new FakeCliOption(false),
        ))->run();

        self::assertSame(
            [
                sprintf('[RUN]  %-20s%5s', 'phpunit', '1/2'),
                sprintf('[RUN]  %-20s%5s', 'sonar', '2/2'),
            ],
            $output->muteds(),
            'parallel run must postpone dependent checks until prerequisites pass',
        );
    }

    #[Test]
    public function reportsSuccessesWhenChecksPass(): void
    {
        $output = new FakeOutput();

        (new ParallelRun(
            new FakeChecks([
                new FakeCheck('syntax', '/dev/null'),
                new FakeCheck('types', '/dev/null'),
            ]),
            $output,
            new FakeCliOption(false),
        ))->run();

        self::assertCount(
            3,
            $output->successes(),
            'parallel run must report both checks and the whole run as passed',
        );
    }

    #[Test]
    public function keepsSuccessfulOutputSilentWhenNotVerbose(): void
    {
        $folder = (new TempFolder())->withFile('quiet.sh', 'echo hidden');

        try {
            self::expectOutputString('');

            (new ParallelRun(
                new FakeChecks([new FakeCheck('quiet', $folder->path() . '/quiet.sh')]),
                new FakeOutput(),
                new FakeCliOption(false),
            ))->run();
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function echoesSuccessfulOutputWhenVerbose(): void
    {
        $folder = (new TempFolder())->withFile('visible.sh', 'echo visible');

        try {
            self::expectOutputString("visible\n");

            (new ParallelRun(
                new FakeChecks([new FakeCheck('visible', $folder->path() . '/visible.sh')]),
                new FakeOutput(),
                new FakeCliOption(true),
            ))->run();
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function echoesFailedOutputWhenNotVerbose(): void
    {
        $folder = (new TempFolder())->withFile('loud.sh', "echo failed\nexit 1");

        try {
            self::expectOutputString("failed\n");
            self::expectException(PiquleException::class);

            (new ParallelRun(
                new FakeChecks([new FakeCheck('loud', $folder->path() . '/loud.sh')]),
                new FakeOutput(),
                new FakeCliOption(false),
            ))->run();
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function reportsFailuresWhenCheckFails(): void
    {
        $folder = (new TempFolder())->withFile('broken.sh', 'exit 1');
        $output = new FakeOutput();

        try {
            try {
                (new ParallelRun(
                    new FakeChecks([new FakeCheck('broken', $folder->path() . '/broken.sh')]),
                    $output,
                    new FakeCliOption(false),
                ))->run();
            } catch (PiquleException) {
            }

            self::assertCount(
                2,
                $output->errors(),
                'parallel run must report the failed check and the whole run failure',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function skipsDependentChecksWhenIndependentCheckFails(): void
    {
        $folder = (new TempFolder())->withFile('syntax.sh', 'exit 1');
        $output = new FakeOutput();

        try {
            try {
                (new ParallelRun(
                    new FakeChecks([
                        new FakeCheck('syntax', $folder->path() . '/syntax.sh'),
                        new FakeCheck('sonar', '/dev/null'),
                    ]),
                    $output,
                    new FakeCliOption(false),
                ))->run();
            } catch (PiquleException) {
            }

            self::assertSame(
                [sprintf('[RUN]  %-20s%5s', 'syntax', '1/2')],
                $output->muteds(),
                'parallel run must not start dependent checks after a failed prerequisite',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function throwsWhenIndependentCheckFails(): void
    {
        $folder = (new TempFolder())->withFile('broken.sh', 'exit 1');

        try {
            self::expectException(PiquleException::class);

            (new ParallelRun(
                new FakeChecks([new FakeCheck('broken', $folder->path() . '/broken.sh')]),
                new FakeOutput(),
                new FakeCliOption(false),
            ))->run();
        } finally {
            $folder->close();
        }
    }
}
