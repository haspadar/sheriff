<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Tests\Integration\Check;

use Haspadar\Piqule\Check\ParallelRun;
use Haspadar\Piqule\Tests\Fake\Check\FakeCheck;
use Haspadar\Piqule\Tests\Fake\Check\FakeChecks;
use Haspadar\Piqule\Tests\Fake\Check\FakeCliOption;
use Haspadar\Piqule\Tests\Fake\Output\FakeOutput;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParallelRunTest extends TestCase
{
    #[Test]
    public function reportsSuccessWhenCheckPasses(): void
    {
        $output = new FakeOutput();

        (new ParallelRun(
            new FakeChecks([new FakeCheck('parallel', '/dev/null')]),
            $output,
            new FakeCliOption(false),
        ))->run();

        self::assertCount(
            2,
            $output->successes(),
            'parallel run must report the check and the whole run as passed',
        );
    }
}
