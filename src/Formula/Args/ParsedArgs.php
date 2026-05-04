<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Args;

use InvalidArgumentException;
use JsonException;
use Override;

/**
 * Decodes a single JSON list literal from the wrapped Args into a typed scalar list.
 */
final readonly class ParsedArgs implements Args
{
    private const int JSON_MAX_DEPTH = 512;

    /**
     * Initializes with the args containing a JSON list literal.
     *
     * @param Args $origin Args carrying a single JSON list literal to decode
     */
    public function __construct(private Args $origin) {}

    #[Override]
    public function values(): array
    {
        $raw = $this->singleRawValue();
        $decoded = $this->decodeJsonList($raw);
        $this->assertScalarList($decoded, $raw);

        return array_values(array_filter($decoded, static fn($item) => is_scalar($item)));
    }

    /**
     * Extracts the single raw string value from the wrapped args.
     *
     * @throws InvalidArgumentException
     */
    private function singleRawValue(): string
    {
        $values = $this->origin->values();

        if ($values === []) {
            throw new InvalidArgumentException(
                'Expected JSON list literal, got empty input',
            );
        }

        if (count($values) !== 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected single JSON list literal, got %d values',
                    count($values),
                ),
            );
        }

        $raw = $values[0];

        if (!is_string($raw)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected JSON list literal string, got %s',
                    get_debug_type($raw),
                ),
            );
        }

        return $raw;
    }

    /**
     * Decodes a JSON string into an array.
     *
     * @throws InvalidArgumentException
     * @return array<array-key, mixed>
     */
    private function decodeJsonList(string $raw): array
    {
        try {
            $decoded = json_decode(
                $raw,
                true,
                self::JSON_MAX_DEPTH,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException) {
            throw new InvalidArgumentException(
                sprintf('Invalid JSON list literal "%s"', $raw),
            );
        }

        if (!is_array($decoded)) {
            throw new InvalidArgumentException(
                sprintf('Expected JSON list literal, got "%s"', $raw),
            );
        }

        return $decoded;
    }

    /**
     * Validates that the decoded array is a flat list of scalars.
     *
     * @param array<array-key, mixed> $decoded
     * @throws InvalidArgumentException
     */
    private function assertScalarList(array $decoded, string $raw): void
    {
        if (!array_is_list($decoded)) {
            throw new InvalidArgumentException(
                sprintf('Expected JSON list literal, got "%s"', $raw),
            );
        }

        foreach ($decoded as $item) {
            if (!is_int($item)
                && !is_float($item)
                && !is_string($item)
                && !is_bool($item)
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'JSON list literal must contain only scalars, got %s',
                        get_debug_type($item),
                    ),
                );
            }
        }
    }
}
