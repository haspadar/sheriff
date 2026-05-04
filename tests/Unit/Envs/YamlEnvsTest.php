<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Envs;

use Haspadar\Sheriff\Envs\YamlEnvs;
use Haspadar\Sheriff\SheriffException;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class YamlEnvsTest extends TestCase
{
    private TempFolder $folder;

    protected function setUp(): void
    {
        $this->folder = new TempFolder();
    }

    protected function tearDown(): void
    {
        $this->folder->close();
    }

    #[Test]
    public function parsesEnvsSection(): void
    {
        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "envs:\n  MY_VAR: \"echo hello\"\n",
        )->path() . '/.sheriff.yaml';

        self::assertSame(
            ['MY_VAR' => 'echo hello'],
            (new YamlEnvs($path))->vars(),
            'YamlEnvs must parse envs section into name => command map',
        );
    }

    #[Test]
    public function parsesMultipleEnvs(): void
    {
        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "envs:\n  A: \"cmd-a\"\n  B: \"cmd-b\"\n",
        )->path() . '/.sheriff.yaml';

        self::assertSame(
            ['A' => 'cmd-a', 'B' => 'cmd-b'],
            (new YamlEnvs($path))->vars(),
            'YamlEnvs must parse multiple envs entries',
        );
    }

    #[Test]
    public function returnsEmptyWhenNoEnvsSection(): void
    {
        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "override:\n  phpstan.level: 8\n",
        )->path() . '/.sheriff.yaml';

        self::assertSame(
            [],
            (new YamlEnvs($path))->vars(),
            'YamlEnvs must return empty array when envs section is absent',
        );
    }

    #[Test]
    public function throwsWhenEnvsSectionIsNotMapping(): void
    {
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "envs: not-a-mapping\n",
        )->path() . '/.sheriff.yaml';

        (new YamlEnvs($path))->vars();
    }

    #[Test]
    public function throwsWhenEnvsValueIsNotString(): void
    {
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "envs:\n  MY_VAR: 42\n",
        )->path() . '/.sheriff.yaml';

        (new YamlEnvs($path))->vars();
    }

    #[Test]
    public function throwsWhenYamlIsMalformed(): void
    {
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "envs:\n  BROKEN: [\n",
        )->path() . '/.sheriff.yaml';

        (new YamlEnvs($path))->vars();
    }

    #[Test]
    public function throwsWhenEnvsNameIsInvalid(): void
    {
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "envs:\n  1INVALID: \"echo hello\"\n",
        )->path() . '/.sheriff.yaml';

        (new YamlEnvs($path))->vars();
    }

    #[Test]
    public function throwsWhenEnvsNameHasInvalidSuffix(): void
    {
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "envs:\n  VALID_START!: \"echo hello\"\n",
        )->path() . '/.sheriff.yaml';

        (new YamlEnvs($path))->vars();
    }

    #[Test]
    public function throwsWhenYamlRootIsNotMapping(): void
    {
        $this->expectException(SheriffException::class);

        $path = $this->folder->withFile(
            '.sheriff.yaml',
            "just a string\n",
        )->path() . '/.sheriff.yaml';

        (new YamlEnvs($path))->vars();
    }
}
