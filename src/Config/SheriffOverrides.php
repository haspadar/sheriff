<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Config;

use Haspadar\Piqule\PiquleException;

/**
 * User overrides with Sheriff rename compatibility aliases.
 */
final readonly class SheriffOverrides
{
    /**
     * Initializes with raw override values from project YAML.
     *
     * @param array<string, mixed> $overrides Raw override values
     */
    public function __construct(private array $overrides) {}

    /**
     * Returns overrides with legacy Piqule keys mapped to Sheriff keys.
     *
     * @throws PiquleException
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if (
            array_key_exists('ci.sheriff_bin', $this->overrides)
            || !array_key_exists('ci.piqule_bin', $this->overrides)
        ) {
            return $this->overrides;
        }

        $legacy = $this->overrides['ci.piqule_bin'];

        if (!is_scalar($legacy) && !is_array($legacy)) {
            throw new PiquleException('Override "ci.piqule_bin" must be scalar or list<scalar>');
        }

        return [...$this->overrides, 'ci.sheriff_bin' => $legacy];
    }
}
