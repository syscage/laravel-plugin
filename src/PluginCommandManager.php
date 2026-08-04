<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use Syscage\Plugin\Contracts\PluginCommandManagerInterface;
use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Default implementation of {@see PluginCommandManagerInterface}.
 *
 * Mirrors the directory-scanning idiom Laravel's own console kernel uses
 * to auto-load "app/Console/Commands", adapted to a plugin's namespace and
 * directory layout. Commands are registered two ways for reliability:
 *
 * - Immediately, via {@see Kernel::registerCommand()}, which adds directly
 *   to the console application's current command list.
 * - Queued via {@see ConsoleApplication::starting()}, which replays on
 *   every future console application rebuild (e.g. after something calls
 *   `Kernel::setArtisan(null)`, as Testbench does when loading migrations
 *   mid-test) — {@see ConsoleApplication::__construct()} re-runs its
 *   bootstrappers on every rebuild, so this keeps registration correct
 *   even when the first console application instance gets discarded.
 */
final class PluginCommandManager implements PluginCommandManagerInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly Application $app,
        private readonly Kernel $kernel,
    ) {
    }

    public function register(PluginInterface $plugin): void
    {
        $directory = $plugin->appPath('Console' . DIRECTORY_SEPARATOR . 'Commands');

        if (! $this->files->isDirectory($directory)) {
            return;
        }

        $classes = $this->discoverCommandClasses($plugin, $directory);

        if ($classes === []) {
            return;
        }

        foreach ($classes as $class) {
            $this->kernel->registerCommand($this->app->make($class));
        }

        $app = $this->app;

        ConsoleApplication::starting(static function (ConsoleApplication $artisan) use ($app, $classes): void {
            foreach ($classes as $class) {
                $artisan->add($app->make($class));
            }
        });
    }

    /**
     * @return array<int, class-string<Command>>
     */
    private function discoverCommandClasses(PluginInterface $plugin, string $directory): array
    {
        $namespace = rtrim($plugin->namespace(), '\\') . '\\Console\\Commands\\';
        $commands = [];

        foreach ($this->files->allFiles($directory) as $file) {
            // getRelativePathname() is resolved by Symfony's Finder relative
            // to the searched-in directory, so it is correct regardless of
            // how the (possibly unnormalized) $directory string was built.
            $class = $namespace . str_replace(
                ['/', '\\', '.php'],
                ['\\', '\\', ''],
                $file->getRelativePathname(),
            );

            if (! class_exists($class)) {
                continue;
            }

            if (is_subclass_of($class, Command::class) && ! (new ReflectionClass($class))->isAbstract()) {
                $commands[] = $class;
            }
        }

        return $commands;
    }
}
