<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Files;

use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Files\EachFile;
use Haspadar\Sheriff\Files\TextFiles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EachFileTest extends TestCase
{
    #[Test]
    public function executesActionForEachFile(): void
    {
        $called = [];

        (new EachFile(
            new TextFiles([
                'a.txt' => 'A',
                'b.txt' => 'B',
            ]),
            function (TextFile $file) use (&$called): void {
                $called[] = $file->name();
            },
        ))->run();

        self::assertSame(
            ['a.txt', 'b.txt'],
            $called,
            'Action must be executed for each file',
        );
    }
}
