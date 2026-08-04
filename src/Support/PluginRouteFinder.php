<?php

declare(strict_types=1);

namespace Syscage\Plugin\Support;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use ReflectionClass;
use ReflectionFunction;
use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Finds the routes contributed by a given plugin, determined by checking
 * whether the route's controller class (or, for closure-based routes, the
 * closure itself) is defined inside that plugin's directory.
 */
final class PluginRouteFinder
{
    /**
     * @return array<int, Route>
     */
    public function forPlugin(Router $router, PluginInterface $plugin): array
    {
        $pluginPath = realpath($plugin->path());

        if ($pluginPath === false) {
            return [];
        }

        return array_values(array_filter(
            iterator_to_array($router->getRoutes()->getIterator()),
            fn (Route $route): bool => $this->belongsToPlugin($route, $pluginPath),
        ));
    }

    private function belongsToPlugin(Route $route, string $pluginPath): bool
    {
        $file = $this->sourceFile($route);

        return $file !== null && str_starts_with($file, $pluginPath);
    }

    private function sourceFile(Route $route): ?string
    {
        $action = $route->getAction('uses');

        if ($action instanceof Closure) {
            $file = (new ReflectionFunction($action))->getFileName();

            return $file !== false ? realpath($file) ?: null : null;
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$class] = explode('@', $action, 2);

            if (class_exists($class)) {
                $file = (new ReflectionClass($class))->getFileName();

                return $file !== false ? realpath($file) ?: null : null;
            }
        }

        return null;
    }
}
