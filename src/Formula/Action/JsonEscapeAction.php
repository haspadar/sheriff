<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\StringifiedArgs;
use InvalidArgumentException;
use JsonException;
use Override;

/**
 * Escapes each value for safe interpolation inside a JSON string literal.
 */
final readonly class JsonEscapeAction implements Action
{
    #[Override]
    public function transformed(Args $args): Args
    {
        try {
            return new ListArgs(
                array_map(
                    static fn(int|float|string|bool $item): string => (string) preg_replace(
                        '/^"|"$/',
                        '',
                        json_encode(
                            (string) $item,
                            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                        ),
                    ),
                    (new StringifiedArgs($args))->values(),
                ),
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                sprintf('Cannot JSON-encode value: %s', $exception->getMessage()),
                0,
                $exception,
            );
        }
    }
}
