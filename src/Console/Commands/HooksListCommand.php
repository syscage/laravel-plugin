<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\HookManagerInterface;

/**
 * Lists every registered hook and the plugin that registered it.
 */
final class HooksListCommand extends Command
{
    protected $signature = 'hooks:list';

    protected $description = 'List every registered hook and the plugin that registered it';

    public function handle(HookManagerInterface $hooks): int
    {
        $rows = $hooks->all();

        if ($rows === []) {
            $this->components->info('No hooks registered.');

            return self::SUCCESS;
        }

        $this->table(['Type', 'Name', 'Priority', 'Owner'], array_map(
            static fn (array $row): array => [$row['type'], $row['name'], $row['priority'] ?? '-', $row['plugin']],
            $rows,
        ));

        return self::SUCCESS;
    }
}
