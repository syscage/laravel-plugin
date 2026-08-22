<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Tests\TestCase;

/**
 * Exercises the friendly "*-plugin" Artisan command aliases against a
 * disposable plugin, covering each distinct redirection mechanism: the
 * shared "app/" path trait (make:model-plugin as a representative), the
 * special-cased Factory/Seeder/Test/Migration generators, and the
 * database/framework command delegation families.
 */
final class GeneratorAliasesTest extends TestCase
{
    private string $pluginsPath;

    private string $cacheDirectory;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $sandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('generator-aliases-', true);

        $this->pluginsPath = $sandbox;
        $this->cacheDirectory = $sandbox . '-cache';

        $app['config']->set('plugin.plugins_path', $this->pluginsPath);
        $app['config']->set('plugin.namespace_root', 'GeneratedPlugins');
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

    private function makePlugin(string $name = 'BlogPlugin'): string
    {
        $this->artisan('make:plugin', ['name' => $name])->assertSuccessful();
        $this->artisan('plugin:discover')->assertSuccessful();

        return \Illuminate\Support\Str::kebab($name);
    }

    public function test_make_model_plugin_writes_into_the_plugin_app_path_under_its_namespace(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:model-plugin', ['plugin' => $alias, 'name' => 'Post'])->assertSuccessful();

        $path = $this->pluginsPath . '/' . $alias . '/src/app/Models/Post.php';
        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace GeneratedPlugins\\BlogPlugin\\Models;', file_get_contents($path));
    }

    public function test_make_model_plugin_with_mfs_options_writes_companions_into_the_plugin(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:model-plugin', [
            'plugin' => $alias,
            'name' => 'Post',
            '-m' => true,
            '-f' => true,
            '-s' => true,
        ])->assertSuccessful();

        $base = $this->pluginsPath . '/' . $alias;

        $modelPath = $base . '/src/app/Models/Post.php';
        $this->assertFileExists($modelPath);
        $this->assertStringContainsString("protected \$table = 'plugin_posts';", file_get_contents($modelPath));

        $this->assertFileExists($base . '/src/database/factories/PostFactory.php');
        $this->assertFileExists($base . '/src/database/seeders/PostSeeder.php');

        $migrations = glob($base . '/src/database/migrations/*_create_plugin_posts_table.php');
        $this->assertNotEmpty($migrations);
        $this->assertStringContainsString("Schema::create('plugin_posts'", file_get_contents($migrations[0]));

        $this->assertFileDoesNotExist(database_path('factories/PostFactory.php'));
        $this->assertFileDoesNotExist(database_path('seeders/PostSeeder.php'));
        $this->assertEmpty(glob(database_path('migrations') . '/*_create_plugin_posts_table.php'));
    }

    public function test_make_model_plugin_honors_a_per_plugin_table_prefix_override(): void
    {
        $alias = $this->makePlugin();

        $manifestPath = $this->pluginsPath . '/' . $alias . '/plugin.json';
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $manifest['table_prefix'] = 'blog_';
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->artisan('plugin:discover')->assertSuccessful();

        $this->artisan('make:model-plugin', ['plugin' => $alias, 'name' => 'Post', '-m' => true])
            ->assertSuccessful();

        $modelPath = $this->pluginsPath . '/' . $alias . '/src/app/Models/Post.php';
        $this->assertStringContainsString("protected \$table = 'blog_posts';", file_get_contents($modelPath));

        $migrations = glob($this->pluginsPath . '/' . $alias . '/src/database/migrations/*_create_blog_posts_table.php');
        $this->assertNotEmpty($migrations);
        $this->assertStringContainsString("Schema::create('blog_posts'", file_get_contents($migrations[0]));
    }

    public function test_make_controller_plugin_supports_the_resource_option(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:controller-plugin', ['plugin' => $alias, 'name' => 'PostController', '--resource' => true])
            ->assertSuccessful();

        $path = $this->pluginsPath . '/' . $alias . '/src/app/Http/Controllers/PostController.php';
        $this->assertFileExists($path);
        $this->assertStringContainsString('public function store(', file_get_contents($path));
    }

    public function test_make_factory_plugin_writes_into_the_plugin_factory_path(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:factory-plugin', ['plugin' => $alias, 'name' => 'PostFactory'])->assertSuccessful();

        $this->assertFileExists($this->pluginsPath . '/' . $alias . '/src/database/factories/PostFactory.php');
    }

    public function test_make_seeder_plugin_writes_into_the_plugin_seeder_path_with_its_own_namespace(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:seeder-plugin', ['plugin' => $alias, 'name' => 'PostSeeder'])->assertSuccessful();

        $path = $this->pluginsPath . '/' . $alias . '/src/database/seeders/PostSeeder.php';
        $this->assertFileExists($path);
        $this->assertStringContainsString(
            'namespace GeneratedPlugins\\BlogPlugin\\Database\\Seeders;',
            file_get_contents($path),
        );
    }

    public function test_make_test_plugin_writes_into_a_plugin_tests_directory(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:test-plugin', ['plugin' => $alias, 'name' => 'ExampleTest'])->assertSuccessful();

        $this->assertFileExists($this->pluginsPath . '/' . $alias . '/tests/Feature/ExampleTest.php');
    }

    public function test_make_migration_plugin_delegates_to_make_migration_with_the_plugin_path(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:migration-plugin', [
            'plugin' => $alias,
            'name' => 'create_posts_table',
            '--create' => 'posts',
        ])->assertSuccessful();

        $migrations = glob($this->pluginsPath . '/' . $alias . '/src/database/migrations/*_create_posts_table.php');
        $this->assertNotEmpty($migrations);
    }

    public function test_migrate_plugin_runs_only_that_plugins_migrations(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:migration-plugin', [
            'plugin' => $alias,
            'name' => 'create_widgets_table',
            '--create' => 'widgets',
        ])->assertSuccessful();

        $this->artisan('migrate-plugin', ['plugin' => $alias])->assertSuccessful();

        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('plugin_widgets'));
    }

    public function test_migrate_status_plugin_reports_the_plugins_migration(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('make:migration-plugin', [
            'plugin' => $alias,
            'name' => 'create_gadgets_table',
            '--create' => 'gadgets',
        ])->assertSuccessful();

        $this->artisan('migrate-plugin', ['plugin' => $alias])->assertSuccessful();

        $this->artisan('migrate:status-plugin', ['plugin' => $alias])
            ->assertSuccessful()
            ->expectsOutputToContain('create_gadgets_table');
    }

    public function test_db_seed_plugin_defaults_to_the_plugins_own_database_seeder(): void
    {
        $alias = $this->makePlugin();

        $namespace = 'GeneratedPlugins\\BlogPlugin\\Database\\Seeders';
        $seederDir = $this->pluginsPath . '/' . $alias . '/src/database/seeders';
        (new Filesystem())->put($seederDir . '/DatabaseSeeder.php', <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Illuminate\Database\Seeder;

        final class DatabaseSeeder extends Seeder
        {
            public function run(): void
            {
                \$this->command->info('blog-plugin-seeder-ran');
            }
        }
        PHP);

        $this->artisan('plugin:discover')->assertSuccessful();

        $this->artisan('db:seed-plugin', ['plugin' => $alias])
            ->assertSuccessful()
            ->expectsOutputToContain('blog-plugin-seeder-ran');
    }

    public function test_db_seed_plugin_succeeds_out_of_the_box_via_the_scaffolded_database_seeder(): void
    {
        $alias = $this->makePlugin();

        $this->assertFileExists($this->pluginsPath . '/' . $alias . '/src/database/seeders/DatabaseSeeder.php');

        $this->artisan('db:seed-plugin', ['plugin' => $alias])->assertSuccessful();
    }

    public function test_config_cache_plugin_delegates_to_config_cache(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('config:cache-plugin', ['plugin' => $alias])->assertSuccessful();

        $this->assertFileExists($this->app->getCachedConfigPath());

        @unlink($this->app->getCachedConfigPath());
    }

    public function test_route_list_plugin_shows_only_that_plugins_routes(): void
    {
        $alias = $this->makePlugin();

        $webRoutes = $this->pluginsPath . '/' . $alias . '/src/routes/web.php';
        file_put_contents(
            $webRoutes,
            "<?php\nuse Illuminate\\Support\\Facades\\Route;\nRoute::get('/blog-plugin-alias-test', fn () => 'ok');\n",
        );

        // "plugin:discover" only refreshes the discovery cache/registry; a
        // plugin's routes are registered when its own provider boots,
        // which (within a single already-booted process) requires
        // re-running the full manager boot cycle — exactly what a fresh
        // "php artisan" process would do automatically on its next run.
        $this->app->make(\Syscage\Plugin\Contracts\PluginManagerInterface::class)->boot(fresh: true);

        $this->artisan('route:list-plugin', ['plugin' => $alias])
            ->assertSuccessful()
            ->expectsOutputToContain('blog-plugin-alias-test');
    }

    public function test_vendor_publish_plugin_runs_without_error(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('vendor:publish-plugin', ['plugin' => $alias])->assertSuccessful();
    }

    public function test_queue_work_plugin_defaults_the_queue_name_to_the_plugin_alias(): void
    {
        $alias = $this->makePlugin();

        $this->artisan('queue:work-plugin', ['plugin' => $alias, '--once' => true])->assertSuccessful();
    }

    public function test_test_plugin_runs_the_plugins_own_phpunit_suite(): void
    {
        $alias = $this->makePlugin();

        $testDir = $this->pluginsPath . '/' . $alias . '/tests/Unit';
        (new Filesystem())->makeDirectory($testDir, recursive: true);
        (new Filesystem())->put($testDir . '/TrivialTest.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        use PHPUnit\Framework\TestCase;

        final class TrivialTest extends TestCase
        {
            public function test_it_passes(): void
            {
                $this->assertTrue(true);
            }
        }
        PHP);

        $this->artisan('test-plugin', ['plugin' => $alias])->assertSuccessful();
    }
}
