<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula\Actions;

use Haspadar\Sheriff\Config\Config;
use Haspadar\Sheriff\Envs\Envs;
use Haspadar\Sheriff\Formula\Action\Action;
use Haspadar\Sheriff\Formula\Action\ConfigAction;
use Haspadar\Sheriff\Formula\Action\EnvsAction;
use Haspadar\Sheriff\Formula\Action\FirstAction;
use Haspadar\Sheriff\Formula\Action\FormatAction;
use Haspadar\Sheriff\Formula\Action\FormatEachAction;
use Haspadar\Sheriff\Formula\Action\IfEmptyAction;
use Haspadar\Sheriff\Formula\Action\IfNotEmptyAction;
use Haspadar\Sheriff\Formula\Action\JoinAction;
use Haspadar\Sheriff\Formula\Action\JsonEscapeAction;
use Haspadar\Sheriff\Formula\Action\ReplaceAction;
use Haspadar\Sheriff\Formula\Action\ShellQuoteAction;
use Haspadar\Sheriff\SheriffException;

/**
 * Complete set of DSL action factories for placeholder resolution.
 *
 * Example:
 *
 *     (new FormulaActions($config, $envs))->map();
 */
final readonly class FormulaActions
{
    /**
     * Accepts context-specific dependencies for stateful actions.
     *
     * @param Config $config Configuration for config() action
     * @param Envs $envs Environment variables for envs() action
     */
    public function __construct(private Config $config, private Envs $envs) {}

    /**
     * Builds the complete action factory map for DSL placeholder resolution.
     *
     * @return array<string, callable(string): Action>
     */
    public function map(): array
    {
        return [
            'config' => fn(string $raw): Action => new ConfigAction($this->config, $raw),
            'envs' => fn(string $raw): Action => new EnvsAction($this->envs, $raw),
            'first' => static fn(string $raw): Action => match (trim($raw)) {
                '' => new FirstAction(),
                default => throw new SheriffException('Action "first" does not accept arguments'),
            },
            'format' => static fn(string $raw): Action => new FormatAction($raw),
            'format_each' => static fn(string $raw): Action => new FormatEachAction($raw),
            'if_empty' => static fn(string $raw): Action => match (trim($raw)) {
                '' => new IfEmptyAction(),
                default => throw new SheriffException('Action "if_empty" does not accept arguments'),
            },
            'if_not_empty' => static fn(string $raw): Action => match (trim($raw)) {
                '' => new IfNotEmptyAction(),
                default => throw new SheriffException('Action "if_not_empty" does not accept arguments'),
            },
            'join' => static fn(string $raw): Action => new JoinAction($raw),
            'json_escape' => static fn(string $raw): Action => match (trim($raw)) {
                '' => new JsonEscapeAction(),
                default => throw new SheriffException('Action "json_escape" does not accept arguments'),
            },
            'replace' => static fn(string $raw): Action => new ReplaceAction($raw),
            'shell_quote' => static fn(string $raw): Action => match (trim($raw)) {
                '' => new ShellQuoteAction(),
                default => throw new SheriffException('Action "shell_quote" does not accept arguments'),
            },
        ];
    }
}
