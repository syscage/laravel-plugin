<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginRouteManagerInterface;

/**
 * Default implementation of {@see PluginRouteManagerInterface}.
 *
 * Mirrors the same "skip when routes are cached" guard that
 * {@see \Illuminate\Support\ServiceProvider::loadRoutesFrom()} applies.
 */
final class PluginRouteManager implements PluginRouteManagerInterface
{
    public function __construct(
        private readonly Router $router,
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function register(PluginInterface $plugin): void
    {
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        $web = $plugin->routePath('web.php');

        if ($this->files->isFile($web)) {
            $this->router->middleware('web')->group($web);
        }

        $api = $plugin->routePath('api.php');

        if ($this->files->isFile($api)) {
            $this->router->prefix('api')->middleware('api')->group($api);
        }

        $console = $plugin->routePath('console.php');

        if ($this->app->runningInConsole() && $this->files->isFile($console)) {
            require $console;
        }
    }
}
