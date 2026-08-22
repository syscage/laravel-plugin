<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Syscage\Plugin\Contracts\PluginCacheInterface;
use Syscage\Plugin\PluginManifestRepository;
use Syscage\Plugin\Support\PluginManifest;
use Syscage\Plugin\Support\StubRenderer;

/**
 * Scaffolds a new plugin's directory structure, "plugin.json" manifest,
 * "composer.json", and core PHP classes.
 */
final class MakePluginCommand extends Command
{
    protected $signature = 'make:plugin
        {name : The plugin name, e.g. "MyPlugin"}
        {--description= : A short description of the plugin}
        {--author= : The plugin author\'s name}';

    protected $description = 'Scaffold a new plugin';

    public function handle(
        Filesystem $files,
        PluginManifestRepository $manifests,
        PluginCacheInterface $cache,
    ): int {
        $name = Str::studly((string) $this->argument('name'));

        if ($name === '') {
            $this->components->error('The plugin name must not be empty.');

            return self::FAILURE;
        }

        $alias = Str::kebab($name);
        $path = rtrim((string) config('plugin.plugins_path'), '/\\') . DIRECTORY_SEPARATOR . $alias;

        if ($files->isDirectory($path)) {
            $this->components->error("A plugin already exists at [{$path}].");

            return self::FAILURE;
        }

        $namespaceRoot = trim((string) config('plugin.namespace_root'), '\\');
        $namespace = $namespaceRoot . '\\' . $name;
        $providerClass = $name . 'PluginServiceProvider';
        $provider = $namespace . '\\Providers\\' . $providerClass;
        $description = (string) ($this->option('description') ?? '');
        $author = $this->option('author');

        $this->createDirectoryStructure($files, $path);

        $stubs = new StubRenderer($files, (string) config('plugin.stubs'));

        $files->put($path . '/src/Plugin.php', $stubs->render('Plugin.stub', [
            'namespace' => $namespace,
        ]));

        $files->put($path . '/src/app/Providers/' . $providerClass . '.php', $stubs->render('PluginServiceProvider.stub', [
            'namespace' => $namespace,
            'providerClass' => $providerClass,
        ]));

        $files->put($path . '/src/routes/web.php', $stubs->render('routes.web.stub', ['name' => $name]));
        $files->put($path . '/src/routes/api.php', $stubs->render('routes.api.stub', ['name' => $name]));
        $files->put($path . '/src/routes/console.php', $stubs->render('routes.console.stub', ['name' => $name]));
        $files->put($path . '/src/config/config.php', $stubs->render('config.stub', []));

        $files->put($path . '/src/database/seeders/DatabaseSeeder.php', $stubs->render('DatabaseSeeder.stub', [
            'namespace' => $namespace,
        ]));

        $files->put($path . '/README.md', $stubs->render('README.stub', [
            'name' => $name,
            'alias' => $alias,
            'description' => $description,
        ]));

        $manifests->write($path . '/plugin.json', PluginManifest::fromArray([
            'id' => null,
            'name' => $name,
            'alias' => $alias,
            'description' => $description,
            'version' => '1.0.0',
            'provider' => $provider,
            'namespace' => $namespace,
            'priority' => 100,
            'enabled' => true,
            'requires' => [],
            'conflicts' => [],
            'permissions' => [],
            'sidebar' => [],
            'authors' => $author !== null ? [['name' => $author]] : [],
        ]));

        $files->put($path . '/composer.json', $this->buildComposerJson($alias, $namespace, $description));

        $cache->forget();

        $this->components->info("Plugin [{$alias}] created at [{$path}].");
        $this->components->twoColumnDetail('Next step', "php artisan plugin:install {$alias}");

        return self::SUCCESS;
    }

    private function createDirectoryStructure(Filesystem $files, string $path): void
    {
        $directories = [
            'src/app/Http/Controllers',
            'src/app/Http/Middleware',
            'src/app/Http/Requests',
            'src/app/Models',
            'src/app/Providers',
            'src/config',
            'src/database/migrations',
            'src/database/factories',
            'src/database/seeders',
            'src/lang',
            'src/public',
            'src/resources/css',
            'src/resources/js',
            'src/resources/views',
            'src/routes',
        ];

        foreach ($directories as $directory) {
            $files->makeDirectory($path . '/' . $directory, recursive: true);
        }
    }

    private function buildComposerJson(string $alias, string $namespace, string $description): string
    {
        $json = json_encode([
            'name' => "plugins/{$alias}",
            'description' => $description,
            'type' => 'laravel-plugin',
            'autoload' => [
                'psr-4' => [
                    $namespace . '\\' => ['src', 'src/app'],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $json . PHP_EOL;
    }
}
