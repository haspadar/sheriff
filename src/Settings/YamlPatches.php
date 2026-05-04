<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings;

use Haspadar\Sheriff\Settings\Patch\AppendPatches;
use Haspadar\Sheriff\Settings\Patch\OverridePatches;
use Haspadar\Sheriff\Settings\Patch\RemovePatches;
use Haspadar\Sheriff\SheriffException;
use TypeError;

/**
 * Loads `.sheriff.yaml` and turns its operations into a Patch list.
 *
 * Example:
 *
 *     (new YamlPatches('/path/to/.sheriff.yaml'))->patches();
 */
final readonly class YamlPatches
{
    private YamlDocument $document;

    /**
     * Initializes with the path to the user yaml file.
     *
     * @param string $path Filesystem path to the user `.sheriff.yaml`
     */
    public function __construct(string $path)
    {
        $this->document = new YamlDocument($path);
    }

    /**
     * Returns every Patch declared by the yaml file in declaration order.
     *
     * @throws SheriffException|TypeError
     * @return list<Patch>
     */
    public function patches(): array
    {
        return [
            ...(new OverridePatches($this->document->section('override')))->patches(),
            ...(new AppendPatches($this->document->section('append')))->patches(),
            ...(new RemovePatches($this->document->section('remove')))->patches(),
        ];
    }
}
