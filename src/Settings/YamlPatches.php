<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Settings;

use Haspadar\Piqule\PiquleException;
use Haspadar\Piqule\Settings\Patch\AppendPatches;
use Haspadar\Piqule\Settings\Patch\OverridePatches;
use Haspadar\Piqule\Settings\Patch\RemovePatches;
use TypeError;

/**
 * Loads `.piqule.yaml` and turns its operations into a Patch list.
 *
 * Example:
 *
 *     (new YamlPatches('/path/to/.piqule.yaml'))->patches();
 */
final readonly class YamlPatches
{
    private YamlDocument $document;

    /**
     * Initializes with the path to the user yaml file.
     *
     * @param string $path Filesystem path to the user `.piqule.yaml`
     */
    public function __construct(string $path)
    {
        $this->document = new YamlDocument($path);
    }

    /**
     * Returns every Patch declared by the yaml file in declaration order.
     *
     * @throws PiquleException|TypeError
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
