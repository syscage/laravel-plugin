<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Testing;

use Composer\Autoload\ClassLoader;
use Illuminate\Console\Command;
use ReflectionClass;
use Symfony\Component\Process\Process;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

/**
 * Runs a plugin's own test suite (its "tests/" directory, as scaffolded by
 * "make:test-plugin") through PHPUnit directly. A plugin's tests are not
 * part of the host application's own test suite, so this deliberately
 * runs a separate PHPUnit process rather than delegating to "artisan
 * test", which only knows about the host application's tests.
 */
final class TestPluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'test-plugin
        {plugin : The plugin whose tests should run}
        {--filter= : Filter which tests run by name}';

    protected $description = "Run a plugin's test suite";

    public function handle(): int
    {
        $plugin = $this->targetPlugin();
        $testsPath = $plugin->path('tests');

        if (! is_dir($testsPath)) {
            $this->components->error("No tests directory found for plugin [{$plugin->alias()}].");

            return self::FAILURE;
        }

        $vendorPath = $this->resolveVendorPath();
        $binary = $vendorPath !== null ? $vendorPath . '/bin/phpunit' : null;

        if ($binary === null || ! is_file($binary)) {
            $this->components->error('PHPUnit was not found in the vendor directory.');

            return self::FAILURE;
        }

        $arguments = [$binary, $testsPath];

        if ($filter = $this->option('filter')) {
            $arguments[] = '--filter=' . $filter;
        }

        $process = new Process($arguments, dirname($vendorPath));
        $process->setTimeout(null);

        return $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });
    }

    /**
     * Resolve the Composer vendor directory via the currently registered
     * class loader, rather than Laravel's own `base_path()` — the two
     * usually coincide, but resolving via Composer directly is correct
     * even when they don't (e.g. non-standard project layouts).
     */
    private function resolveVendorPath(): ?string
    {
        foreach (spl_autoload_functions() as $function) {
            if (is_array($function) && $function[0] instanceof ClassLoader) {
                $classLoaderFile = (new ReflectionClass($function[0]))->getFileName();

                if ($classLoaderFile === false) {
                    return null;
                }

                // $classLoaderFile is ".../vendor/composer/ClassLoader.php".
                return dirname($classLoaderFile, 2);
            }
        }

        return null;
    }
}
