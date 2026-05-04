<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

/**
 * Extracts the requested check name from CLI arguments.
 */
final readonly class RequestedCheck
{
    private const array FLAGS = [
        '-v', '--verbose',
        '-p', '--parallel', '-P', '--no-parallel',
        '-f', '--full', '-F', '--no-full',
    ];

    /**
     * Initializes with the CLI argument list.
     *
     * @param list<string> $argv Raw CLI argument vector, including the script name
     */
    public function __construct(private array $argv) {}

    /** Returns the check name or empty string if none requested. */
    public function name(): string
    {
        $args = array_values(
            array_filter(
                $this->argv,
                static fn(string $a): bool => !in_array($a, self::FLAGS, true),
            ),
        );

        return $args[1] ?? '';
    }
}
