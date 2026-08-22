<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;
use Syscage\Plugin\Contracts\DashboardWidgetCacheInterface;
use Syscage\Plugin\Contracts\FrontendDetectorInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginManifestRepositoryInterface;
use Syscage\Plugin\Support\PluginManifest;
use Syscage\Plugin\Support\StubRenderer;

/**
 * Scaffolds a new dashboard widget class (and, for a React/Vue/Inertia
 * host, its frontend companion component) inside a plugin, registering it
 * in the plugin's "plugin.json" "widgets" manifest entry.
 */
final class MakeWidgetPluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'make:widget-plugin
        {plugin : The plugin to generate into}
        {name : The widget name, e.g. "MessagesToday"}
        {--title= : The widget\'s human-readable title}
        {--permission= : The permission required to view the widget}';

    protected $description = 'Scaffold a new dashboard widget inside a plugin';

    public function handle(
        Filesystem $files,
        PluginManifestRepositoryInterface $manifests,
        FrontendDetectorInterface $frontend,
        DashboardWidgetCacheInterface $cache,
    ): int {
        $plugin = $this->targetPlugin();
        $name = Str::studly((string) $this->argument('name'));

        if ($name === '') {
            $this->components->error('The widget name must not be empty.');

            return self::FAILURE;
        }

        $className = $name . 'Widget';
        $namespace = rtrim($plugin->namespace(), '\\') . '\\Widgets';
        $classPath = $plugin->appPath('Widgets' . DIRECTORY_SEPARATOR . $className . '.php');

        if ($files->isFile($classPath)) {
            $this->components->error("A widget already exists at [{$classPath}].");

            return self::FAILURE;
        }

        $id = $plugin->alias() . '.' . Str::kebab($name);
        $title = (string) ($this->option('title') ?? Str::headline($name));
        $permission = $this->option('permission');
        $component = Str::studly(str_replace('-', ' ', $plugin->alias())) . '/Widgets/' . $name;

        $stubs = new StubRenderer($files, (string) config('plugin.stubs'));

        $directory = dirname($classPath);

        if (! $files->isDirectory($directory)) {
            $files->makeDirectory($directory, recursive: true);
        }

        $files->put($classPath, $stubs->render('widgets/DashboardWidget.stub', [
            'namespace' => $namespace,
            'class' => $className,
            'id' => $id,
            'title' => $title,
            'component' => $component,
            'permission' => $permission !== null ? "'" . addslashes((string) $permission) . "'" : 'null',
        ]));

        $this->writeFrontendComponent($files, $stubs, $frontend->detect(), $plugin, $name);

        $fqcn = $namespace . '\\' . $className;

        if (! in_array($fqcn, $plugin->widgets(), true)) {
            $manifest = PluginManifest::fromArray($plugin->toArray())->withWidgets([...$plugin->widgets(), $fqcn]);

            $manifests->write($plugin->path((string) config('plugin.manifest_file', 'plugin.json')), $manifest);
        }

        $cache->forget();

        $this->components->info("Widget [{$id}] created at [{$classPath}].");

        return self::SUCCESS;
    }

    private function writeFrontendComponent(
        Filesystem $files,
        StubRenderer $stubs,
        string $frontend,
        PluginInterface $plugin,
        string $name,
    ): void {
        $stub = match ($frontend) {
            'vue', 'inertia-vue' => 'widgets/Widget.vue.stub',
            'react', 'inertia-react' => 'widgets/Widget.tsx.stub',
            default => null,
        };

        if ($stub === null) {
            return;
        }

        $extension = str_ends_with($stub, '.vue.stub') ? 'vue' : 'tsx';
        $path = $plugin->resourcePath('js' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR . $name . '.' . $extension);

        if ($files->isFile($path)) {
            return;
        }

        $directory = dirname($path);

        if (! $files->isDirectory($directory)) {
            $files->makeDirectory($directory, recursive: true);
        }

        $files->put($path, $stubs->render($stub, ['name' => $name]));
    }
}
