<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Config\Dirs;

/**
 * A list of directories transformed for use in a config value
 */
interface Dirs
{
    /** @return list<string> */
    public function toList(): array;
}
