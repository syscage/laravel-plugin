<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Syscage\Plugin\Contracts\PluginSeederManagerInterface;
use Syscage\Plugin\Tests\TestCase;

final class PluginSeederManagerTest extends TestCase
{
    private string $pluginsPath;

    private string $cacheDirectory;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $sandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-seeder-', true);

        $this->pluginsPath = $sandbox;
        $this->cacheDirectory = $sandbox . '-cache';

        $app['config']->set('plugin.plugins_path', $this->pluginsPath);
        $app['config']->set('plugin.namespace_root', 'SeederPlugins');
        $app['config']->set('plugin.cache.plugins', $this->cacheDirectory . '/plugins.php');
        $app['config']->set('plugin.cache.sidebar', $this->cacheDirectory . '/sidebar.php');
        $app['config']->set('plugin.public_path', $this->cacheDirectory . '/public');
    }

    protected function tearDown(): void
    {
        $files = new Filesystem();
        $files->deleteDirectory($this->pluginsPath);
        $files->deleteDirectory($this->cacheDirectory);

        parent::tearDown();
    }

    public function test_plain_db_seed_also_seeds_enabled_plugins(): void
    {
        $this->createEnabledPluginWithSeeder();

        $output = new BufferedOutput();
        $this->app->make(Dispatcher::class)->dispatch(
            new CommandFinished('db:seed', new ArrayInput([]), $output, 0),
        );

        $this->assertStringContainsString('blog-seeder-ran', $output->fetch());
    }

    public function test_db_seed_with_an_explicit_class_does_not_expand_to_plugins(): void
    {
        $this->createEnabledPluginWithSeeder();

        $output = new BufferedOutput();
        $input = new ArrayInput(['--class' => 'Database\\Seeders\\DatabaseSeeder']);
        $this->app->make(Dispatcher::class)->dispatch(new CommandFinished('db:seed', $input, $output, 0));

        $this->assertStringNotContainsString('blog-seeder-ran', $output->fetch());
    }

    public function test_it_returns_the_aliases_of_seeded_plugins(): void
    {
        $alias = $this->createEnabledPluginWithSeeder();

        $seeded = $this->app->make(PluginSeederManagerInterface::class)->seedEnabled(null, new BufferedOutput());

        $this->assertSame([$alias], $seeded);
    }

    public function test_db_seed_plugin_without_an_argument_seeds_every_enabled_plugin(): void
    {
        $this->createEnabledPluginWithSeeder();

        $this->artisan('db:seed-plugin')
            ->assertSuccessful()
            ->expectsOutputToContain('blog-seeder-ran');
    }

    public function test_db_seed_plugin_without_an_argument_rejects_an_explicit_class(): void
    {
        $this->artisan('db:seed-plugin', ['--class' => 'Foo'])->assertFailed();
    }

    public function test_db_seed_plugin_targets_the_given_plugin_on_every_call_in_one_process(): void
    {
        $this->createEnabledPluginWithSeeder();
        $this->createEnabledPluginWithSeeder('ShopPlugin', 'shop-seeder-ran');

        $this->artisan('db:seed-plugin', ['plugin' => 'blog-plugin', '--force' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('blog-seeder-ran');

        $this->artisan('db:seed-plugin', ['plugin' => 'shop-plugin', '--force' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('shop-seeder-ran');
    }

    public function test_db_seed_plugin_skips_a_plugin_without_a_database_seeder(): void
    {
        $alias = $this->createEnabledPluginWithSeeder('BarePlugin');
        (new Filesystem())->delete($this->pluginsPath . "/{$alias}/src/database/seeders/DatabaseSeeder.php");

        $this->artisan('db:seed-plugin', ['plugin' => $alias, '--force' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('has no DatabaseSeeder to run');
    }

    private function createEnabledPluginWithSeeder(string $name = 'BlogPlugin', string $marker = 'blog-seeder-ran'): string
    {
        $alias = Str::kebab($name);

        $this->artisan('make:plugin', ['name' => $name])->assertSuccessful();

        $namespace = "SeederPlugins\\{$name}\\Database\\Seeders";
        (new Filesystem())->put($this->pluginsPath . "/{$alias}/src/database/seeders/DatabaseSeeder.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Illuminate\Database\Seeder;

        final class DatabaseSeeder extends Seeder
        {
            public function run(): void
            {
                \$this->command->info('{$marker}');
            }
        }
        PHP);

        $this->artisan('plugin:discover')->assertSuccessful();
        $this->artisan('migrate')->assertSuccessful();
        $this->artisan('plugin:install', ['alias' => $alias])->assertSuccessful();
        $this->artisan('plugin:enable', ['alias' => $alias])->assertSuccessful();

        return $alias;
    }
}
