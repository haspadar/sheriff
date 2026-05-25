<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Xml;

use Haspadar\Sheriff\Chain\Rendered;
use Override;

/**
 * Renders one PHPUnit `<testsuite>` block with its directory entries.
 *
 * Example:
 *
 *     (new PhpunitSuiteBlock('unit', ['tests/Unit']))->rendered();
 */
final readonly class PhpunitSuiteBlock implements Rendered
{
    private const string INDENT = '        ';

    private const string DIR_INDENT = '            ';

    /**
     * Initializes with the suite name and the directory paths it covers.
     *
     * @param string $name Value of the testsuite name attribute
     * @param list<string> $paths Directory paths relative to the project root
     */
    public function __construct(private string $name, private array $paths) {}

    #[Override]
    public function rendered(): string
    {
        $lines = [sprintf('%s<testsuite name="%s">', self::INDENT, $this->name)];

        foreach ($this->paths as $path) {
            $lines[] = sprintf(
                '%s<directory suffix="Test.php">../../%s</directory>',
                self::DIR_INDENT,
                $path,
            );
        }

        $lines[] = sprintf('%s</testsuite>', self::INDENT);

        return implode("\n", $lines);
    }
}
