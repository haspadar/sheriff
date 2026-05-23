<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\File;

use Haspadar\Sheriff\File\TemplateFile;
use Haspadar\Sheriff\File\TextFile;
use Haspadar\Sheriff\Settings\DefaultSettings;
use Haspadar\Sheriff\Settings\Patch\OverrideList;
use Haspadar\Sheriff\Settings\PatchedSettings;
use Haspadar\Sheriff\Settings\Value\ListValue;
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
        self::assertThat(
            new TemplateFile(
                new TextFile('matrix.yml', 'php: [{% ListText(php.versions)|Joined(", ") %}]'),
                new PatchedSettings(
                    new DefaultSettings(),
                    new OverrideList('php.versions', new ListValue([
                        new StringValue('8.3'),
                        new StringValue('8.4'),
                    ])),
                ),
            ),
            new HasFileContents('php: [8.3, 8.4]'),
            'TemplateFile must render ListText joined from settings using the given separator',
        );
    }

    #[Test]
    public function rendersListValueWithEachFormattedFromDefaultSettings(): void
    {
        self::assertThat(
            new TemplateFile(
                new TextFile(
                    'docker.yml',
                    'image: {% ListText(php.versions)|EachFormatted("%s-alpine")|Joined(" ") %}',
                ),
                new PatchedSettings(
                    new DefaultSettings(),
                    new OverrideList('php.versions', new ListValue([
                        new StringValue('8.3'),
                        new StringValue('8.4'),
                    ])),
                ),
            ),
            new HasFileContents('image: 8.3-alpine 8.4-alpine'),
            'TemplateFile must apply EachFormatted to each list item before joining',
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
                . "        testsPaths:\n"
                . "            - \"*/tests/*\"\n"
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
