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

        return [...$this->overrides, 'ci.sheriff_bin' => $this->legacy()];
    }

    /**
     * Returns a validated legacy override value.
     *
     * @throws PiquleException
     * @return scalar|list<scalar>
     */
    private function legacy(): array|bool|float|int|string
    {
        if (!array_key_exists('ci.piqule_bin', $this->overrides)) {
            throw new PiquleException('Override "ci.piqule_bin" must be scalar or list<scalar>');
        }

        $legacy = $this->overrides['ci.piqule_bin'];

        if (is_scalar($legacy)) {
            return $legacy;
        }

        if (!is_array($legacy) || !array_is_list($legacy)) {
            throw new PiquleException('Override "ci.piqule_bin" must be scalar or list<scalar>');
        }

        $values = [];

        foreach ($legacy as $value) {
            if (!is_scalar($value)) {
                throw new PiquleException('Override "ci.piqule_bin" must be scalar or list<scalar>');
            }

            $values[] = $value;
        }

        return $values;
    }
}
