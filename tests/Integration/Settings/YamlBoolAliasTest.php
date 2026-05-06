<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Settings;

use Haspadar\Sheriff\Chain\Plain\BoolText;
use Haspadar\Sheriff\Settings\DefaultSettings;
use Haspadar\Sheriff\Settings\PatchedSettings;
use Haspadar\Sheriff\Settings\YamlPatches;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Documents which YAML scalars Symfony's parser treats as a boolean and which
 * stay strings. The tightened `[ "$CLOUD" = "true" ]` check in sonar/command.sh
 * relies on `BoolText` rendering `true`/`false` only — anything that does not
 * survive the `<yaml> -> BoolValue -> BoolText` round-trip would silently fall
 * through to the local scanner branch.
 */
final class YamlBoolAliasTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function aliases(): iterable
    {
        yield 'true' => ['true', 'true'];
        yield 'false' => ['false', 'false'];
    }

    #[Test]
    #[DataProvider('aliases')]
    public function rendersYamlBooleanAsCanonicalLiteral(string $alias, string $expected): void
    {
        $folder = (new TempFolder())->withFile(
            '.sheriff.yaml',
            sprintf("override:\n    sonar.cloud: %s\n", $alias),
        );

        try {
            self::assertSame(
                $expected,
                (new BoolText(
                    (new PatchedSettings(
                        new DefaultSettings(),
                        ...(new YamlPatches($folder->path() . '/.sheriff.yaml'))->patches(),
                    ))->value('sonar.cloud'),
                ))->rendered(),
                sprintf('YAML scalar "%s" must round-trip to BoolText literal "%s"', $alias, $expected),
            );
        } finally {
            $folder->close();
        }
    }
}
