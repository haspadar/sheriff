<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Configuration value holding an associative map from string keys to nested values.
 *
 * Example:
 *
 *     new TreeValue([
 *         'haspadar' => new TreeValue([
 *             'afferentCoupling' => new TreeValue([
 *                 'ignoreAbstract' => new BoolValue(true),
 *             ]),
 *         ]),
 *     ]);
 */
final readonly class TreeValue implements Value
{
    /**
     * Initializes with the tree payload.
     *
     * @param array<string, Value> $entries Key-to-value map carried by this tree
     */
    public function __construct(public array $entries) {}
}
