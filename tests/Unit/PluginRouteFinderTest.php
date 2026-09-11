<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Support\PluginRouteFinder;
use Syscage\Plugin\Tests\Support\FakePlugin;

final class PluginRouteFinderTest extends TestCase
{
    private string $rootPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rootPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('route-finder-', true);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->rootPath);

        parent::tearDown();
    }

    /**
     * A plain `str_starts_with()` on the plugin's path would wrongly treat
     * "plugins/user" as a prefix match for "plugins/user-role", attributing
     * every "user-role" route to the "user" plugin instead.
     */
    public function test_it_does_not_confuse_a_plugin_with_a_sibling_whose_alias_it_prefixes(): void
    {
        $userPath = $this->rootPath . '/user';
        $userRolePath = $this->rootPath . '/user-role';

        (new Filesystem())->ensureDirectoryExists($userPath);
        (new Filesystem())->ensureDirectoryExists($userRolePath);

        $roleController = $this->defineController($userRolePath, 'Controller' . bin2hex(random_bytes(6)));

        $user = new FakePlugin(alias: 'user', basePath: $userPath);
        $userRole = new FakePlugin(alias: 'user-role', basePath: $userRolePath);

        $router = new Router(new Dispatcher());
        $router->get('role', ['uses' => $roleController . '@index'])->name('role.index');

        $finder = new PluginRouteFinder();

        $this->assertSame([], $finder->forPlugin($router, $user));
        $this->assertCount(1, $finder->forPlugin($router, $userRole));

        $route = iterator_to_array($router->getRoutes())[0];

        $this->assertSame('user-role', $finder->ownerAlias($route, [$user, $userRole]));
    }

    private function defineController(string $directory, string $className): string
    {
        $fqcn = 'Syscage\\Plugin\\Tests\\Fixtures\\RouteFinder\\' . $className;

        (new Filesystem())->put($directory . '/' . $className . '.php', <<<PHP
        <?php
        namespace Syscage\\Plugin\\Tests\\Fixtures\\RouteFinder;
        final class {$className}
        {
            public function index() {}
        }
        PHP);

        require_once $directory . '/' . $className . '.php';

        return $fqcn;
    }
}
