<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

require_once __DIR__ . '/../Fixtures/plugins/demo-plugin/src/Plugin.php';

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Events\PluginDisabled;
use Syscage\Plugin\Events\PluginEnabled;
use Syscage\Plugin\Events\PluginInstalled;
use Syscage\Plugin\Events\PluginUninstalled;
use Syscage\Plugin\Events\PluginUpdated;
use Syscage\Plugin\Exceptions\PluginAlreadyInstalledException;
use Syscage\Plugin\Exceptions\PluginNotInstalledException;
use Syscage\Plugin\PluginDependencyResolver;
use Syscage\Plugin\PluginLifecycle;
use Syscage\Plugin\PluginManifestRepository;
use Syscage\Plugin\PluginRecordRepository;
use Syscage\Plugin\PluginRegistry;
use Syscage\Plugin\Tests\Fixtures\DemoPlugin\Plugin as DemoPlugin;
use Syscage\Plugin\Tests\TestCase;

final class PluginLifecycleTest extends TestCase
{
    private string $pluginPath;

    private PluginRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluginPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-lifecycle-', true);
        mkdir($this->pluginPath, recursive: true);

        file_put_contents($this->pluginPath . '/plugin.json', json_encode([
            'id' => null,
            'name' => 'DemoPlugin',
            'alias' => 'demo-plugin',
            'description' => 'A fixture plugin used by the test suite.',
            'version' => '1.0.0',
            'provider' => 'Syscage\\Plugin\\Tests\\Fixtures\\DemoPlugin\\Providers\\DemoPluginServiceProvider',
            'namespace' => 'Syscage\\Plugin\\Tests\\Fixtures\\DemoPlugin',
            'priority' => 50,
            'enabled' => true,
            'requires' => [],
            'conflicts' => [],
            'permissions' => [],
            'sidebar' => [],
            'authors' => [],
        ]));

        $this->registry = new PluginRegistry();
    }

    protected function tearDown(): void
    {
        @unlink($this->pluginPath . '/plugin.json');
        @rmdir($this->pluginPath);

        parent::tearDown();
    }

    private function makePlugin(): PluginInterface
    {
        $manifest = (new PluginManifestRepository(new Filesystem()))->read($this->pluginPath . '/plugin.json');
        $plugin = new DemoPlugin($manifest, $this->pluginPath);

        $this->registry->register($plugin);

        return $plugin;
    }

    private function makeLifecycle(): PluginLifecycle
    {
        return new PluginLifecycle(
            new PluginRecordRepository(),
            new PluginManifestRepository(new Filesystem()),
            new PluginDependencyResolver(),
            $this->registry,
            $this->app->make(Dispatcher::class),
            'plugin.json',
        );
    }

    public function test_install_creates_a_record_and_writes_the_uuid_back_to_the_manifest(): void
    {
        Event::fake([PluginInstalled::class]);

        $plugin = $this->makePlugin();
        $lifecycle = $this->makeLifecycle();

        $lifecycle->install($plugin);

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'demo-plugin']);

        $updated = $this->makePlugin();
        $this->assertNotNull($updated->id());

        Event::assertDispatched(PluginInstalled::class);
    }

    public function test_install_throws_when_already_installed(): void
    {
        $plugin = $this->makePlugin();
        $lifecycle = $this->makeLifecycle();

        $lifecycle->install($plugin);

        $this->expectException(PluginAlreadyInstalledException::class);

        $lifecycle->install($this->makePlugin());
    }

    public function test_uninstall_removes_the_record_and_forgets_the_registry_entry(): void
    {
        Event::fake([PluginUninstalled::class]);

        $plugin = $this->makePlugin();
        $lifecycle = $this->makeLifecycle();

        $lifecycle->install($plugin);
        $lifecycle->uninstall($plugin);

        $this->assertDatabaseMissing(config('plugin.table'), ['alias' => 'demo-plugin']);
        $this->assertFalse($this->registry->has('demo-plugin'));

        Event::assertDispatched(PluginUninstalled::class);
    }

    public function test_uninstall_throws_when_not_installed(): void
    {
        $lifecycle = $this->makeLifecycle();

        $this->expectException(PluginNotInstalledException::class);

        $lifecycle->uninstall($this->makePlugin());
    }

    public function test_disable_then_enable_toggles_state_and_manifest(): void
    {
        Event::fake([PluginDisabled::class, PluginEnabled::class]);

        $plugin = $this->makePlugin();
        $lifecycle = $this->makeLifecycle();

        $lifecycle->install($plugin);
        $lifecycle->disable($plugin);

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'demo-plugin', 'enabled' => false]);
        $this->assertFalse($this->makePlugin()->enabled());

        $lifecycle->enable($plugin);

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'demo-plugin', 'enabled' => true]);
        $this->assertTrue($this->makePlugin()->enabled());

        Event::assertDispatched(PluginDisabled::class);
        Event::assertDispatched(PluginEnabled::class);
    }

    public function test_update_persists_the_new_version(): void
    {
        Event::fake([PluginUpdated::class]);

        $plugin = $this->makePlugin();
        $lifecycle = $this->makeLifecycle();

        $lifecycle->install($plugin);
        $lifecycle->update($plugin, '0.9.0');

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'demo-plugin', 'version' => '1.0.0']);

        Event::assertDispatched(PluginUpdated::class, fn (PluginUpdated $event): bool => $event->oldVersion === '0.9.0');
    }
}
