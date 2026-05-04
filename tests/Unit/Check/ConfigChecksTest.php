<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ConfigChecks;
use Haspadar\Sheriff\Tests\Fake\Config\FakeConfig;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigChecksTest extends TestCase
{
    #[Test]
    public function yieldsCheckWhenCommandFileExists(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff/phpstan/command.sh',
            '#!/bin/bash',
        );

        $checks = new ConfigChecks(
            new FakeConfig(['phpstan.cli' => [true]]),
            $folder->path(),
        );

        try {
            $names = [];
            foreach ($checks->all() as $check) {
                $names[] = $check->name();
            }

            self::assertSame(
                ['phpstan'],
                $names,
                'ConfigChecks must yield checks with existing command files',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function skipsKeysNotEndingWithCli(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff/phpstan/command.sh',
            '#!/bin/bash',
        );

        $checks = new ConfigChecks(
            new FakeConfig([
                'phpstan.level' => [9],
                'phpstan.cli' => [true],
            ]),
            $folder->path(),
        );

        try {
            $names = [];
            foreach ($checks->all() as $check) {
                $names[] = $check->name();
            }

            self::assertSame(
                ['phpstan'],
                $names,
                'ConfigChecks must skip config keys not ending with .cli',
            );
        } finally {
            $folder->close();
        }
    }

    #[Test]
    public function skipsCheckWhenCommandFileMissing(): void
    {
        $checks = new ConfigChecks(
            new FakeConfig(['phpstan.cli' => [true]]),
            '/nonexistent',
        );

        $names = [];
        foreach ($checks->all() as $check) {
            $names[] = $check->name();
        }

        self::assertSame(
            [],
            $names,
            'ConfigChecks must skip checks without command files',
        );
    }
}
