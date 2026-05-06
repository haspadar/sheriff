<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Check;

use Haspadar\Sheriff\Check\ConfigChecks;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Tests\Fake\Settings\FakeSettings;
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
            new FakeSettings(['phpstan.cli' => new BoolValue(true)]),
            $folder->path(),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        $folder->close();

        self::assertSame(
            ['phpstan'],
            $names,
            'ConfigChecks must yield checks with existing command files',
        );
    }

    #[Test]
    public function skipsKeysNotEndingWithCli(): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff/phpstan/command.sh',
            '#!/bin/bash',
        );

        $checks = new ConfigChecks(
            new FakeSettings([
                'phpstan.level' => new IntValue(9),
                'phpstan.cli' => new BoolValue(true),
            ]),
            $folder->path(),
        );

        $names = array_map(
            static fn($c) => $c->name(),
            iterator_to_array($checks->all()),
        );

        $folder->close();

        self::assertSame(
            ['phpstan'],
            $names,
            'ConfigChecks must skip settings keys not ending with .cli',
        );
    }

    #[Test]
    public function skipsCheckWhenCommandFileMissing(): void
    {
        $checks = new ConfigChecks(
            new FakeSettings(['phpstan.cli' => new BoolValue(true)]),
            '/nonexistent',
        );

        self::assertSame(
            [],
            iterator_to_array($checks->all()),
            'ConfigChecks must skip checks without command files',
        );
    }
}
