<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Check;

/**
 * Human-readable elapsed time formatting.
 */
final readonly class ElapsedTime
{
    private const int SECONDS_PER_MINUTE = 60;

    /**
     * Initializes with elapsed seconds.
     *
     * @param float $seconds Wall-clock duration to format
     */
    public function __construct(private float $seconds) {}

    /** Formats as "1.2s" or "2m05s". */
    public function formatted(): string
    {
        $rounded = round($this->seconds, 1);

        if ($rounded < self::SECONDS_PER_MINUTE) {
            return sprintf('%.1fs', $rounded);
        }

        $total = (int) round($this->seconds);

        return sprintf('%dm%02ds', intdiv($total, self::SECONDS_PER_MINUTE), $total % self::SECONDS_PER_MINUTE);
    }
}
