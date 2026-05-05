<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\File;

use Haspadar\Sheriff\File\TemplateFile;
use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Settings\DefaultSettings;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFileContents;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TemplateFileTest extends TestCase
{
    #[Test]
    public function rendersStringValueFromDefaultSettings(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('hadolint.yml', 'failure-threshold: << StringText(hadolint.failure_threshold) >>'),
                new DefaultSettings(),
            ),
            new HasFileContents('failure-threshold: error'),
            'TemplateFile must render StringText key from DefaultSettings',
        );
    }

    #[Test]
    public function rendersIntValueFromDefaultSettings(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('phpstan.neon', 'level: << IntText(phpstan.level) >>'),
                new DefaultSettings(),
            ),
            new HasFileContents('level: 9'),
            'TemplateFile must render IntText key from DefaultSettings',
        );
    }

    #[Test]
    public function rendersListValueJoinedFromDefaultSettings(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('matrix.yml', 'php: [<< ListText(php.versions)|Joined(", ") >>]'),
                new DefaultSettings(),
            ),
            new HasFileContents('php: [8.3]'),
            'TemplateFile must render ListText joined from DefaultSettings',
        );
    }

    #[Test]
    public function rendersListValueWithEachFormattedFromDefaultSettings(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('docker.yml', 'image: << ListText(php.versions)|EachFormatted("%s-alpine")|Joined(" ") >>'),
                new DefaultSettings(),
            ),
            new HasFileContents('image: 8.3-alpine'),
            'TemplateFile must render EachFormatted pipeline from DefaultSettings',
        );
    }

    #[Test]
    public function rendersBoolValueFromDefaultSettings(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('codecov.yml', 'cloud: << BoolText(codecov.cloud) >>'),
                new DefaultSettings(),
            ),
            new HasFileContents('cloud: true'),
            'TemplateFile must render BoolText key from DefaultSettings',
        );
    }
}
