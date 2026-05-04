<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Action;

use Haspadar\Sheriff\Formula\Args\Args;
use Haspadar\Sheriff\Formula\Args\ListArgs;
use Haspadar\Sheriff\Formula\Args\StringifiedArgs;
use Haspadar\Sheriff\Formula\Args\UnquotedArgs;
use Override;

/**
 * Applies a sprintf template to each incoming value individually.
 */
final readonly class FormatEachAction implements Action
{
    /**
     * Initializes with the raw sprintf template string.
     *
     * @param string $raw Raw sprintf template, including any quoting
     */
    public function __construct(private string $raw) {}

    #[Override]
    public function transformed(Args $args): Args
    {
        $templateArgs = new UnquotedArgs(new ListArgs([$this->raw]));
        $templateValues = $templateArgs->values();
        $template = (string) ($templateValues[0] ?? '');

        return new ListArgs(
            array_map(
                static fn(int|float|string|bool $item): string => sprintf($template, (string) $item),
                (new StringifiedArgs($args))->values(),
            ),
        );
    }
}
