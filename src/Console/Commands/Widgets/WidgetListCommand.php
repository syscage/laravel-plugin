<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Widgets;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\DashboardWidgetManagerInterface;

/**
 * Lists every registered dashboard widget.
 */
final class WidgetListCommand extends Command
{
    protected $signature = 'widget:list';

    protected $description = 'List every registered dashboard widget';

    public function handle(DashboardWidgetManagerInterface $widgets): int
    {
        $all = $widgets->all();

        if ($all === []) {
            $this->components->info('No dashboard widgets have been registered.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($all as $widget) {
            $rows[] = [
                $widget->id(),
                $widget->title(),
                $widget->type()->value,
                $widget->permission() ?? '—',
                (string) $widget->defaultWidth(),
            ];
        }

        $this->table(['Id', 'Title', 'Type', 'Permission', 'Default Width'], $rows);

        return self::SUCCESS;
    }
}
