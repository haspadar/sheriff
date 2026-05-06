<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

use Haspadar\Sheriff\Settings\Settings;
use Override;

/**
 * Discovers available checks from settings keys ending in ".cli".
 */
final readonly class ConfigChecks implements Checks
{
    private const string CLI_SUFFIX = '.cli';

    /**
     * Initializes with project settings and root path.
     *
     * @param Settings $settings Settings providing the set of ".cli" keys
     * @param string $root Absolute path to the project root directory
     */
    public function __construct(private Settings $settings, private string $root) {}

    #[Override]
    public function all(): iterable
    {
        foreach ($this->settings->keys() as $key) {
            if (!str_ends_with($key, self::CLI_SUFFIX)) {
                continue;
            }

            $name = substr($key, 0, -strlen(self::CLI_SUFFIX));
            $check = new ConfigCheck($name, $this->root);

            if (file_exists($check->command())) {
                yield $check;
            }
        }
    }
}
