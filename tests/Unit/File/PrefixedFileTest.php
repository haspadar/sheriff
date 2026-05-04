<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\File;

use Haspadar\Sheriff\File\PrefixedFile;
use Haspadar\Sheriff\File\TextFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrefixedFileTest extends TestCase
{
    #[Test]
    public function keepsFileContentsUnchanged(): void
    {
        self::assertSame(
            '{"enabled":true}',
            (new PrefixedFile(
                '.env',
                new TextFile(
                    'vars/app.settings.json',
                    '{"enabled":true}',
                ),
            ))->contents(),
            'File contents must not be modified',
        );
    }

    #[Test]
    public function prefixesFileNameWithoutExtraSlashes(): void
    {
        self::assertSame(
            '.config/tools/setup.sh',
            (new PrefixedFile(
                '.config',
                new TextFile(
                    'tools/setup.sh',
                    "#!/usr/bin/env bash\nset -e\n",
                ),
            ))->name(),
            'File name must be prefixed',
        );
    }

    #[Test]
    public function trimsTrailingSlashFromPrefix(): void
    {
        self::assertSame(
            '.config/bin/install.sh',
            (new PrefixedFile(
                '.config/',
                new TextFile(
                    'bin/install.sh',
                    "#!/usr/bin/env sh\nprintf install\n",
                ),
            ))->name(),
            'Trailing slash in prefix must be ignored',
        );
    }

    #[Test]
    public function trimsLeadingSlashFromFileName(): void
    {
        self::assertSame(
            '.env/runtime/app.env',
            (new PrefixedFile(
                '.env',
                new TextFile(
                    '/runtime/app.env',
                    "KEY=value\n",
                ),
            ))->name(),
            'Leading slash in file name must be ignored',
        );
    }

    #[Test]
    public function ignoresEmptyPrefix(): void
    {
        self::assertSame(
            'root/config.yaml',
            (new PrefixedFile(
                '',
                new TextFile(
                    'root/config.yaml',
                    "value: true\n",
                ),
            ))->name(),
            'Empty prefix must not introduce leading slash',
        );
    }

    #[Test]
    public function preservesOriginMode(): void
    {
        $file = new PrefixedFile(
            '.config',
            new TextFile(
                'scripts/install.sh',
                "#!/bin/custom-shell\necho ok\n",
                0o755,
            ),
        );

        self::assertSame(
            0o755,
            $file->mode(),
            'PrefixedFile must preserve origin mode',
        );
    }
}
