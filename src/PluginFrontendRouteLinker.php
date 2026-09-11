<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Syscage\Plugin\Contracts\PluginFrontendRouteLinkerInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Support\PluginRouteFinder;

/**
 * Default implementation of {@see PluginFrontendRouteLinkerInterface}.
 *
 * `wayfinder:generate` writes every named route into one fixed directory
 * (`resources/js/routes/`), grouped by route *name* rather than by which
 * plugin registered the route, with no idea a plugin system exists at all.
 *
 * This class runs immediately after wayfinder finishes and physically
 * relocates whatever it just wrote for a route-name directory that belongs
 * exclusively to one plugin into that plugin's own `src/resources/js/routes`
 * — no symlink or junction is left behind anywhere in the host application;
 * the original file is overwritten with a small `export * from '...'`
 * (and, where the moved file has one, `export { default } from '...'`)
 * pointing at the new location, so existing imports of
 * `resources/js/routes/...` keep working unchanged.
 *
 * A route-name top segment does not reliably belong to one plugin (e.g. a
 * host route named "security.edit" and a plugin's "security.user.status.*"
 * routes both land under `resources/js/routes/security/`), so ownership is
 * resolved directory-by-directory, starting from the shallowest: a node is
 * relocated only when every route at or below it belongs to the same single
 * plugin, otherwise its children are checked individually. When a plugin
 * owns exactly one such directory, its *contents* become the plugin's
 * entire routes directory (no redundant alias-named folder inside it); a
 * plugin that happens to own more than one disjoint directory keeps each
 * nested under its own relative path to avoid collisions between them.
 *
 * Every generated file imports the shared `wayfinder` helper via a relative
 * path computed purely from the route name's dot-count, so a moved file's
 * import is rewritten to a fresh relative path back to the single, central,
 * untouched `resources/js/wayfinder` helper — no `wayfinder/` folder is
 * duplicated into any plugin.
 *
 * Generated route files are disposable build output — wayfinder fully
 * regenerates them on every run — so a plugin's routes directory is wiped
 * and rebuilt from scratch every time this runs, with no persisted state
 * needed between calls.
 */
final class PluginFrontendRouteLinker implements PluginFrontendRouteLinkerInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly Router $router,
        private readonly PluginRouteFinder $routes,
        private readonly string $routesPath,
        private readonly string $wayfinderHelperPath,
    ) {
    }

    public function register(iterable $plugins): void
    {
        $plugins = is_array($plugins) ? array_values($plugins) : iterator_to_array($plugins, false);

        $pluginsByAlias = [];

        foreach ($plugins as $plugin) {
            $pluginsByAlias[$plugin->alias()] = $plugin;
        }

        $relativePathsByAlias = [];

        foreach ($this->resolveLinks($plugins) as $relativePath => $alias) {
            $relativePathsByAlias[$alias][] = $relativePath;
        }

        foreach ($relativePathsByAlias as $alias => $relativePaths) {
            $plugin = $pluginsByAlias[$alias] ?? null;

            if ($plugin === null) {
                continue;
            }

            $destinationRoot = $plugin->resourcePath('js/routes');
            $flatten = count($relativePaths) === 1;

            $this->files->deleteDirectory($destinationRoot);
            $this->files->ensureDirectoryExists($destinationRoot);

            foreach ($relativePaths as $relativePath) {
                $this->relocate($relativePath, $flatten ? '' : $relativePath, $destinationRoot);
            }
        }
    }

    /**
     * @param array<int, PluginInterface> $plugins
     * @return array<string, string> relative directory path (forward
     *                                slashes) => owning plugin alias
     */
    private function resolveLinks(array $plugins): array
    {
        $candidates = [];

        foreach ($this->router->getRoutes() as $route) {
            $name = $route->getName();

            if ($name === null || ! str_contains($name, '.')) {
                continue;
            }

            $segments = explode('.', $name);
            array_pop($segments);

            $candidates[] = [
                'path' => $segments,
                'owner' => $this->routes->ownerAlias($route, $plugins),
            ];
        }

        return $this->exclusiveDirectories([], $candidates);
    }

    /**
     * Recursively finds, starting from the shallowest, every directory node
     * under which every route belongs to the same single plugin.
     *
     * @param array<int, string> $prefix
     * @param array<int, array{path: array<int, string>, owner: ?string}> $candidates
     * @return array<string, string>
     */
    private function exclusiveDirectories(array $prefix, array $candidates): array
    {
        $owners = array_values(array_unique(array_map(
            static fn (array $candidate): ?string => $candidate['owner'],
            $candidates,
        )));

        if ($prefix !== [] && count($owners) === 1 && $owners[0] !== null) {
            return [implode('/', $prefix) => $owners[0]];
        }

        $depth = count($prefix);
        $childGroups = [];

        foreach ($candidates as $candidate) {
            if (count($candidate['path']) <= $depth) {
                continue; // this route's own generated file lands directly in $prefix
            }

            $childGroups[$candidate['path'][$depth]][] = $candidate;
        }

        $links = [];

        foreach ($childGroups as $segment => $childCandidates) {
            $links += $this->exclusiveDirectories([...$prefix, $segment], $childCandidates);
        }

        return $links;
    }

    /**
     * Moves every file wayfinder just wrote under $sourceRelativePath into
     * $destinationRoot (optionally nested under $destinationPrefix),
     * rewriting its wayfinder-helper import, and leaves a re-export behind
     * at the original path.
     */
    private function relocate(string $sourceRelativePath, string $destinationPrefix, string $destinationRoot): void
    {
        $sourceDir = $this->joinPath($this->routesPath, $sourceRelativePath);

        if (! $this->files->isDirectory($sourceDir)) {
            return;
        }

        foreach ($this->files->allFiles($sourceDir) as $file) {
            $subPath = str_replace('\\', '/', $file->getRelativePathname());
            $destinationPath = $this->joinPath($destinationRoot, $this->joinPath($destinationPrefix, $subPath));

            $this->files->ensureDirectoryExists(dirname($destinationPath));

            $rewritten = $this->rewriteWayfinderImport($file->getContents(), dirname($destinationPath));

            $this->files->put($destinationPath, $rewritten);
            $this->files->put($file->getPathname(), $this->stubFor($rewritten, dirname($file->getPathname()), $destinationPath));
        }
    }

    private function rewriteWayfinderImport(string $content, string $fileDirectory): string
    {
        $importPath = $this->relativePath($fileDirectory, $this->wayfinderHelperPath);

        return preg_replace_callback(
            '/from (["\'])([.\/]+wayfinder)\1/',
            static fn (array $matches): string => 'from ' . $matches[1] . $importPath . $matches[1],
            $content,
        ) ?? $content;
    }

    private function stubFor(string $movedContent, string $stubDirectory, string $destinationPath): string
    {
        $importPath = $this->relativePath($stubDirectory, preg_replace('/\.ts$/', '', $destinationPath));

        $lines = ["export * from '{$importPath}';"];

        if (str_contains($movedContent, 'export default')) {
            $lines[] = "export { default } from '{$importPath}';";
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function joinPath(string $base, string $relative): string
    {
        if ($relative === '') {
            return $base;
        }

        if ($base === '') {
            return str_replace('/', DIRECTORY_SEPARATOR, $relative);
        }

        return rtrim($base, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    private function relativePath(string $from, string $to): string
    {
        $from = str_replace('\\', '/', rtrim($from, '/\\'));
        $to = str_replace('\\', '/', rtrim($to, '/\\'));

        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        while ($fromParts !== [] && $toParts !== [] && $fromParts[0] === $toParts[0]) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        $relative = str_repeat('../', count($fromParts)) . implode('/', $toParts);

        return str_starts_with($relative, '.') ? $relative : './' . $relative;
    }
}
