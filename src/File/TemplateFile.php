<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\File;

use Haspadar\Sheriff\Chain\Parse\PipelineFormulas;
use Haspadar\Sheriff\Chain\Parse\PipelineOp;
use Haspadar\Sheriff\Settings\Settings;
use Haspadar\Sheriff\SheriffException;
use Override;
use TypeError;

/**
 * Replaces Chain template placeholders in the wrapped file.
 */
final readonly class TemplateFile implements File
{
    /**
     * Initializes with the file and settings used by template pipelines.
     *
     * @param File $origin File whose contents may contain Chain placeholders
     * @param Settings $settings Settings context read by source formulas
     */
    public function __construct(private File $origin, private Settings $settings) {}

    #[Override]
    public function name(): string
    {
        return $this->origin->name();
    }

    #[Override]
    public function contents(): string
    {
        return (string) preg_replace_callback(
            '/<<\s*(.*?)\s*>>/s',
            fn(array $match): string => $this->replaced($match[1]),
            $this->origin->contents(),
        );
    }

    #[Override]
    public function mode(): int
    {
        return $this->origin->mode();
    }

    /**
     * Renders one placeholder expression through Chain.
     *
     * @throws SheriffException
     */
    private function replaced(string $expression): string
    {
        try {
            return (new PipelineOp(
                (new PipelineFormulas($expression))->formulas(),
                $this->settings,
            ))->rendered();
        } catch (SheriffException | TypeError $e) {
            throw new SheriffException(
                sprintf(
                    'File "%s", pipeline "%s": %s',
                    $this->name(),
                    trim($expression),
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }
}
