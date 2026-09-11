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

    /**
     * The alias of whichever of the given plugins owns the route (i.e. its
     * controller, or closure, is defined inside that plugin's directory),
     * or `null` if it belongs to none of them (a host-application route).
     *
     * @param iterable<PluginInterface> $plugins
     */
    public function ownerAlias(Route $route, iterable $plugins): ?string
    {
        $file = $this->sourceFile($route);

        if ($file === null) {
            return null;
        }

        foreach ($plugins as $plugin) {
            $pluginPath = realpath($plugin->path());

            if ($pluginPath !== false && $this->isUnderPath($file, $pluginPath)) {
                return $plugin->alias();
            }
        }

        return null;
    }

    private function belongsToPlugin(Route $route, string $pluginPath): bool
    {
        $file = $this->sourceFile($route);

        return $file !== null && $this->isUnderPath($file, $pluginPath);
    }

    /**
     * Whether $file lives inside the $pluginPath directory. A plain
     * `str_starts_with()` would wrongly match a sibling plugin whose alias
     * happens to start with the same characters (e.g. "plugins/user" is a
     * string-prefix of "plugins/user-role"), so this requires either an
     * exact match or a full path-segment boundary right after $pluginPath.
     */
    private function isUnderPath(string $file, string $pluginPath): bool
    {
        return $file === $pluginPath || str_starts_with($file, rtrim($pluginPath, '/\\') . DIRECTORY_SEPARATOR);
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
