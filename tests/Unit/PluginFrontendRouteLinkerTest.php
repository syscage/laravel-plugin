<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\PluginFrontendRouteLinker;
use Syscage\Plugin\Support\PluginRouteFinder;
use Syscage\Plugin\Tests\Support\FakePlugin;

final class PluginFrontendRouteLinkerTest extends TestCase
{
    private Filesystem $files;

    private string $appPath;

    private string $routesPath;

    private string $wayfinderHelperPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem();
        $this->appPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('route-linker-app-', true);
        $this->routesPath = $this->appPath . '/resources/js/routes';
        $this->wayfinderHelperPath = $this->appPath . '/resources/js/wayfinder';

        $this->files->ensureDirectoryExists($this->routesPath);
        $this->files->ensureDirectoryExists($this->wayfinderHelperPath);
        $this->files->put($this->wayfinderHelperPath . '/index.ts', 'export {};');
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->appPath);

        parent::tearDown();
    }

    public function test_it_flattens_a_single_exclusive_directory_into_the_plugins_routes_root(): void
    {
        $plugin = $this->definePlugin('user-plugin');
        $controller = $this->defineController($plugin);

        $this->writeGeneratedFile('user/index.ts', <<<'TS'
        import { queryParams, type RouteQueryOptions } from './../../wayfinder'

        export const marker = 'USER_INDEX_MARKER';

        export default { marker };
        TS);

        $router = new Router(new Dispatcher());
        $router->get('user', ['uses' => $controller . '@index'])->name('user.index');

        $this->makeLinker($router)->register([$plugin]);

        $destination = $plugin->resourcePath('js/routes/index.ts');
        $this->assertFileExists($destination);

        $destinationContent = file_get_contents($destination);
        $this->assertStringContainsString('USER_INDEX_MARKER', $destinationContent);
        $this->assertStringNotContainsString("from './../../wayfinder'", $destinationContent);
        $this->assertMatchesRegularExpression("/from '[^']*\/wayfinder'/", $destinationContent);

        $expectedImportPath = $this->relativePathBetween(
            dirname($destination),
            $this->wayfinderHelperPath,
        );
        $this->assertStringContainsString("from '{$expectedImportPath}'", $destinationContent);

        // No redundant "user/" folder inside the plugin's own routes root.
        $this->assertFileDoesNotExist($plugin->resourcePath('js/routes/user'));

        // No wayfinder helper duplicated into the plugin.
        $this->assertDirectoryDoesNotExist($plugin->resourcePath('js/wayfinder'));

        $stub = file_get_contents($this->routesPath . '/user/index.ts');
        $this->assertStringContainsString("export * from '", $stub);
        $this->assertStringContainsString("export { default } from '", $stub);
        $this->assertStringNotContainsString('USER_INDEX_MARKER', $stub);
    }

    public function test_it_leaves_a_mixed_directory_alone_but_relocates_its_exclusive_child(): void
    {
        $plugin = $this->definePlugin('status-plugin');
        $controller = $this->defineController($plugin);

        $this->writeGeneratedFile('security/index.ts', <<<'TS'
        export const edit = () => 'CORE_SECURITY_EDIT';
        TS);
        $this->writeGeneratedFile('security/user/status/index.ts', <<<'TS'
        import { queryParams, type RouteQueryOptions } from './../../../../wayfinder'

        export const index = () => 'STATUS_INDEX';
        TS);

        $router = new Router(new Dispatcher());
        $router->get('security', fn () => null)->name('security.edit');
        $router->get('security/user/status', ['uses' => $controller . '@index'])->name('security.user.status.index');

        $this->makeLinker($router)->register([$plugin]);

        // The host's own "security.edit" route is untouched.
        $this->assertStringContainsString('CORE_SECURITY_EDIT', file_get_contents($this->routesPath . '/security/index.ts'));

        // Its exclusive "security/user" child became the plugin's routes root.
        $destination = $plugin->resourcePath('js/routes/status/index.ts');
        $this->assertFileExists($destination);
        $this->assertStringContainsString('STATUS_INDEX', file_get_contents($destination));

        $stub = file_get_contents($this->routesPath . '/security/user/status/index.ts');
        $this->assertStringNotContainsString('STATUS_INDEX', $stub);
        $this->assertStringContainsString("export * from '", $stub);
    }

    public function test_it_is_idempotent_across_repeated_wayfinder_runs(): void
    {
        $plugin = $this->definePlugin('user-plugin');
        $controller = $this->defineController($plugin);

        $router = new Router(new Dispatcher());
        $router->get('user', ['uses' => $controller . '@index'])->name('user.index');

        $generated = <<<'TS'
        import { queryParams, type RouteQueryOptions } from './../../wayfinder'

        export const marker = 'USER_INDEX_MARKER';
        TS;

        // Simulates wayfinder fully regenerating its real output on every
        // run, before this linker relocates whatever is exclusive.
        $this->writeGeneratedFile('user/index.ts', $generated);
        $this->makeLinker($router)->register([$plugin]);

        $this->writeGeneratedFile('user/index.ts', $generated);
        $this->makeLinker($router)->register([$plugin]);

        $destination = $plugin->resourcePath('js/routes/index.ts');
        $this->assertFileExists($destination);
        $this->assertStringContainsString('USER_INDEX_MARKER', file_get_contents($destination));
    }

    private function makeLinker(Router $router): PluginFrontendRouteLinker
    {
        return new PluginFrontendRouteLinker(
            new Filesystem(),
            $router,
            new PluginRouteFinder(),
            $this->routesPath,
            $this->wayfinderHelperPath,
        );
    }

    private function relativePathBetween(string $from, string $to): string
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

    private function writeGeneratedFile(string $relativePath, string $content): void
    {
        $path = $this->routesPath . '/' . $relativePath;
        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);
    }

    private function definePlugin(string $alias): FakePlugin
    {
        $pluginPath = $this->appPath . '/plugins/' . $alias;
        $this->files->ensureDirectoryExists($pluginPath);

        return new FakePlugin(alias: $alias, basePath: $pluginPath);
    }

    private function defineController(FakePlugin $plugin): string
    {
        $className = 'Controller' . bin2hex(random_bytes(6));
        $fqcn = 'Syscage\\Plugin\\Tests\\Fixtures\\RouteLinker\\' . $className;

        $this->files->put($plugin->path($className . '.php'), <<<PHP
        <?php
        namespace Syscage\\Plugin\\Tests\\Fixtures\\RouteLinker;
        final class {$className}
        {
            public function index() {}
        }
        PHP);

        require_once $plugin->path($className . '.php');

        return $fqcn;
    }
}
