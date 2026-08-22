<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Framework;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;
use Syscage\Plugin\Support\PluginRouteFinder;

/**
 * Lists only the routes contributed by a single plugin.
 */
final class RouteListPluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'route:list-plugin {plugin : The plugin whose routes should be listed}';

    protected $description = "List a plugin's registered routes";

    public function handle(Router $router, PluginRouteFinder $finder): int
    {
        $plugin = $this->targetPlugin();

        $rows = array_map(
            fn (Route $route): array => [
                implode('|', $route->methods()),
                $route->uri(),
                (string) $route->getName(),
                $this->actionLabel($route),
            ],
            $finder->forPlugin($router, $plugin),
        );

        if ($rows === []) {
            $this->components->info("No routes found for plugin [{$plugin->alias()}].");

            return self::SUCCESS;
        }

        $this->table(['Method', 'URI', 'Name', 'Action'], $rows);

        return self::SUCCESS;
    }

    private function actionLabel(Route $route): string
    {
        $action = $route->getActionName();

        return $action === 'Closure' ? 'Closure' : $action;
    }
}
