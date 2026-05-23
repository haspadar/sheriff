<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Files;

use ArrayObject;
use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\Files\EachFile;
use Haspadar\Sheriff\Files\TextFiles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EachFileTest extends TestCase
{
    #[Test]
    public function executesActionForEachFile(): void
    {
        /** @var ArrayObject<int, string> $called */
        $called = new ArrayObject();

        (new EachFile(
            new TextFiles([
                'a.txt' => 'A',
                'b.txt' => 'B',
            ]),
            static function (File $file) use ($called): void {
                $called->append($file->name());
            },
        ))->run();

        self::assertSame(
            ['a.txt', 'b.txt'],
            $called->getArrayCopy(),
            'Action must be executed for each file',
        );
    }
}
