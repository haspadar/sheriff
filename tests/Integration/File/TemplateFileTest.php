<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\File;

use Haspadar\Sheriff\File\TemplateFile;
use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Settings\DefaultSettings;
use Haspadar\Sheriff\Settings\Value\StringValue;
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
                new TextFile('hadolint.yml', 'failure-threshold: {% StringText(hadolint.failure_threshold) %}'),
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
                new TextFile('phpmd.xml', 'cyclomatic: {% IntText(phpmd.cyclomatic) %}'),
                new DefaultSettings(),
            ),
            new HasFileContents('cyclomatic: 10'),
            'TemplateFile must render IntText key from DefaultSettings',
        );
    }

    #[Test]
    public function rendersListValueJoinedFromDefaultSettings(): void
    {
        $settings = new DefaultSettings();
        $versions = array_map(
            static fn(StringValue $v): string => $v->raw,
            $settings->value('php.versions')->children,
        );

        self::assertThat(
            new TemplateFile(
                new TextFile('matrix.yml', 'php: [{% ListText(php.versions)|Joined(", ") %}]'),
                $settings,
            ),
            new HasFileContents(sprintf('php: [%s]', implode(', ', $versions))),
            'TemplateFile must render ListText joined from DefaultSettings',
        );
    }

    #[Test]
    public function rendersListValueWithEachFormattedFromDefaultSettings(): void
    {
        $settings = new DefaultSettings();
        $versions = array_map(
            static fn(StringValue $v): string => $v->raw . '-alpine',
            $settings->value('php.versions')->children,
        );

        self::assertThat(
            new TemplateFile(
                new TextFile('docker.yml', 'image: {% ListText(php.versions)|EachFormatted("%s-alpine")|Joined(" ") %}'),
                $settings,
            ),
            new HasFileContents(sprintf('image: %s', implode(' ', $versions))),
            'TemplateFile must render EachFormatted pipeline from DefaultSettings',
        );
    }

    #[Test]
    public function rendersBoolValueFromDefaultSettings(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('codecov.yml', 'cloud: {% BoolText(codecov.cloud) %}'),
                new DefaultSettings(),
            ),
            new HasFileContents('cloud: true'),
            'TemplateFile must render BoolText key from DefaultSettings',
        );
    }

    #[Test]
    public function rendersPhpstanParametersTreeAsNeonBlock(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile('phpstan.neon', '{% NeonTree(phpstan.parameters) %}'),
                new DefaultSettings(),
            ),
            new HasFileContents(
                "\n"
                . "    level: 9\n"
                . "    errorFormat: table\n"
                . "    reportUnmatchedIgnoredErrors: true\n"
                . "    checkUninitializedProperties: true\n"
                . "    checkClassCaseSensitivity: true\n"
                . "    checkDynamicProperties: true\n"
                . "    exceptions:\n"
                . "        checkedExceptionClasses:\n"
                . "            - \\Throwable\n"
                . "    haspadar:\n"
                . "        afferentCoupling:\n"
                . "            ignoreInterfaces: true\n"
                . "            excludedClasses: []\n"
                . "        prohibitStaticMethods:\n"
                . "            allowNamedConstructors: true",
            ),
            'TemplateFile must render the phpstan.parameters TreeValue as a nested neon block, including bare strings and block-style lists',
        );
    }
}
