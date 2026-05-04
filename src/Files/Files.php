<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Files;

use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\SheriffException;
use UnexpectedValueException;

/**
 * A collection of files that exposes an iterable via all()
 */
interface Files
{
    /**
     * Returns all files in this collection.
     *
     * @throws SheriffException|UnexpectedValueException
     * @return iterable<File>
     */
    public function all(): iterable;
}
