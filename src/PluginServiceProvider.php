<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Syscage\Plugin\Console\Commands\Database\DbSeedPluginCommand;
use Syscage\Plugin\Console\Commands\Database\DbWipePluginCommand;
use Syscage\Plugin\Console\Commands\Database\MigrateFreshPluginCommand;
use Syscage\Plugin\Console\Commands\Database\MigratePluginCommand;
use Syscage\Plugin\Console\Commands\Database\MigrateRefreshPluginCommand;
use Syscage\Plugin\Console\Commands\Database\MigrateResetPluginCommand;
use Syscage\Plugin\Console\Commands\Database\MigrateRollbackPluginCommand;
use Syscage\Plugin\Console\Commands\Database\MigrateStatusPluginCommand;
use Syscage\Plugin\Console\Commands\Framework\ConfigCachePluginCommand;
use Syscage\Plugin\Console\Commands\Framework\EventCachePluginCommand;
use Syscage\Plugin\Console\Commands\Framework\OptimizePluginCommand;
use Syscage\Plugin\Console\Commands\Framework\RouteCachePluginCommand;
use Syscage\Plugin\Console\Commands\Framework\RouteListPluginCommand;
use Syscage\Plugin\Console\Commands\Framework\VendorPublishPluginCommand;
use Syscage\Plugin\Console\Commands\Framework\ViewCachePluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeCastPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeControllerPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeEnumPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeEventPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeFactoryPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeJobPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeListenerPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeMailPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeMiddlewarePluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeMigrationPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeModelPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeNotificationPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeObserverPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakePolicyPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeProviderPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeRequestPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeResourcePluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeRulePluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeSeederPluginCommand;
use Syscage\Plugin\Console\Commands\Generators\MakeTestPluginCommand;
use Syscage\Plugin\Console\Commands\MakePluginCommand;
use Syscage\Plugin\Console\Commands\PluginCacheCommand;
use Syscage\Plugin\Console\Commands\PluginClearCommand;
use Syscage\Plugin\Console\Commands\PluginDisableCommand;
use Syscage\Plugin\Console\Commands\PluginDiscoverCommand;
use Syscage\Plugin\Console\Commands\PluginDoctorCommand;
use Syscage\Plugin\Console\Commands\PluginEnableCommand;
use Syscage\Plugin\Console\Commands\PluginInfoCommand;
use Syscage\Plugin\Console\Commands\PluginInstallCommand;
use Syscage\Plugin\Console\Commands\PluginListCommand;
use Syscage\Plugin\Console\Commands\PluginPublishCommand;
use Syscage\Plugin\Console\Commands\PluginUninstallCommand;
use Syscage\Plugin\Console\Commands\PluginUpdateCommand;
use Syscage\Plugin\Console\Commands\Queue\QueueWorkPluginCommand;
use Syscage\Plugin\Console\Commands\Testing\TestPluginCommand;
use Syscage\Plugin\Contracts\FrontendDetectorInterface;
use Syscage\Plugin\Contracts\FrontendManifestGeneratorInterface;
use Syscage\Plugin\Contracts\PluginAssetManagerInterface;
use Syscage\Plugin\Contracts\PluginAutoloaderInterface;
use Syscage\Plugin\Contracts\PluginCacheInterface;
use Syscage\Plugin\Contracts\PluginCommandManagerInterface;
use Syscage\Plugin\Contracts\PluginConfigManagerInterface;
use Syscage\Plugin\Contracts\PluginDependencyResolverInterface;
use Syscage\Plugin\Contracts\PluginDiscoveryInterface;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginLoaderInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginManifestRepositoryInterface;
use Syscage\Plugin\Contracts\PluginMigrationManagerInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Contracts\PluginRegistryInterface;
use Syscage\Plugin\Contracts\PluginRouteManagerInterface;
use Syscage\Plugin\Contracts\PluginSidebarManagerInterface;
use Syscage\Plugin\Contracts\PluginTranslationManagerInterface;
use Syscage\Plugin\Contracts\PluginViewManagerInterface;
use Syscage\Plugin\Support\PluginRouteFinder;

/**
 * The package's main service provider.
 *
 * Binds every framework contract to its default implementation, publishes
 * the package's configuration and migration, and boots the plugin manager
 * so every discovered, enabled plugin is loaded on every request.
 */
final class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/plugin.php', 'plugin');

        $this->registerCoreServices();
        $this->registerResourceManagers();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/plugin.php' => config_path('plugin.php'),
            ], 'plugin-config');

            $this->publishesMigrations([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'plugin-migrations');

            $this->publishes([
                __DIR__ . '/../stubs/plugin' => base_path('stubs/plugin'),
            ], 'plugin-stubs');

            $this->commands([
                MakePluginCommand::class,
                PluginListCommand::class,
                PluginDiscoverCommand::class,
                PluginCacheCommand::class,
                PluginClearCommand::class,
                PluginEnableCommand::class,
                PluginDisableCommand::class,
                PluginInstallCommand::class,
                PluginUninstallCommand::class,
                PluginUpdateCommand::class,
                PluginPublishCommand::class,
                PluginDoctorCommand::class,
                PluginInfoCommand::class,
                ...$this->generatorCommands(),
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Deferred until the application has finished booting: plugin
        // loading registers each plugin's own service provider, and a
        // provider registered mid-boot only has its own boot() called
        // immediately if the application is already marked as booted.
        $this->app->booted(function (): void {
            $this->app->make(PluginManagerInterface::class)->boot();
        });
    }

    private function registerCoreServices(): void
    {
        $this->app->singleton(PluginAutoloaderInterface::class, PluginAutoloader::class);
        $this->app->singleton(PluginManifestRepositoryInterface::class, PluginManifestRepository::class);
        $this->app->singleton(PluginRegistryInterface::class, PluginRegistry::class);
        $this->app->singleton(PluginDependencyResolverInterface::class, PluginDependencyResolver::class);
        $this->app->singleton(PluginLoaderInterface::class, PluginLoader::class);
        $this->app->singleton(PluginRecordRepositoryInterface::class, PluginRecordRepository::class);

        $this->app->singleton(PluginCacheInterface::class, static fn (Application $app): PluginCache => new PluginCache(
            $app->make(Filesystem::class),
            $app->make('config')->get('plugin.cache.plugins'),
        ));

        $this->app->singleton(PluginDiscoveryInterface::class, static fn (Application $app): PluginDiscovery => new PluginDiscovery(
            $app->make(Filesystem::class),
            $app->make(PluginManifestRepositoryInterface::class),
            $app->make(PluginAutoloaderInterface::class),
            $app->make(PluginCacheInterface::class),
            $app->make(PluginRegistryInterface::class),
            $app->make('config')->get('plugin.plugins_path'),
            $app->make('config')->get('plugin.manifest_file'),
        ));

        $this->app->singleton(PluginManagerInterface::class, PluginManager::class);

        $this->app->singleton(PluginLifecycleInterface::class, static fn (Application $app): PluginLifecycle => new PluginLifecycle(
            $app->make(PluginRecordRepositoryInterface::class),
            $app->make(PluginManifestRepositoryInterface::class),
            $app->make(PluginDependencyResolverInterface::class),
            $app->make(PluginRegistryInterface::class),
            $app->make(Dispatcher::class),
            $app->make('config')->get('plugin.manifest_file'),
        ));

        $this->app->singleton(PluginSidebarManagerInterface::class, static fn (Application $app): PluginSidebarManager => new PluginSidebarManager(
            $app->make(Filesystem::class),
            $app->make('config')->get('plugin.cache.sidebar'),
        ));

        $this->app->singleton(FrontendDetectorInterface::class, static fn (Application $app): FrontendDetector => new FrontendDetector(
            $app->make(Filesystem::class),
            $app->basePath('package.json'),
            $app->make('config')->get('plugin.frontend'),
        ));

        $this->app->singleton(FrontendManifestGeneratorInterface::class, static fn (Application $app): FrontendManifestGenerator => new FrontendManifestGenerator(
            $app->make(Filesystem::class),
            $app->make(Router::class),
            $app->make(PluginRouteFinder::class),
            $app->make('config')->get('plugin.cache.frontend'),
        ));
    }

    private function registerResourceManagers(): void
    {
        $this->app->singleton(PluginRouteManagerInterface::class, PluginRouteManager::class);
        $this->app->singleton(PluginViewManagerInterface::class, PluginViewManager::class);
        $this->app->singleton(PluginTranslationManagerInterface::class, PluginTranslationManager::class);
        $this->app->singleton(PluginMigrationManagerInterface::class, PluginMigrationManager::class);
        $this->app->singleton(PluginConfigManagerInterface::class, PluginConfigManager::class);
        $this->app->singleton(PluginCommandManagerInterface::class, PluginCommandManager::class);

        $this->app->singleton(PluginAssetManagerInterface::class, static fn (Application $app): PluginAssetManager => new PluginAssetManager(
            $app->make(Filesystem::class),
            $app->make('config')->get('plugin.public_path'),
        ));
    }

    /**
     * The friendly "*-plugin" Artisan command aliases, each redirecting a
     * Laravel built-in command's output/scope into a target plugin.
     *
     * @return array<int, class-string>
     */
    private function generatorCommands(): array
    {
        return [
            MakeModelPluginCommand::class,
            MakeControllerPluginCommand::class,
            MakeMiddlewarePluginCommand::class,
            MakeRequestPluginCommand::class,
            MakeProviderPluginCommand::class,
            MakePolicyPluginCommand::class,
            MakeObserverPluginCommand::class,
            MakeEventPluginCommand::class,
            MakeListenerPluginCommand::class,
            MakeMailPluginCommand::class,
            MakeNotificationPluginCommand::class,
            MakeJobPluginCommand::class,
            MakeRulePluginCommand::class,
            MakeResourcePluginCommand::class,
            MakeCastPluginCommand::class,
            MakeEnumPluginCommand::class,
            MakeFactoryPluginCommand::class,
            MakeSeederPluginCommand::class,
            MakeMigrationPluginCommand::class,
            MakeTestPluginCommand::class,
            MigratePluginCommand::class,
            MigrateFreshPluginCommand::class,
            MigrateRollbackPluginCommand::class,
            MigrateRefreshPluginCommand::class,
            MigrateResetPluginCommand::class,
            MigrateStatusPluginCommand::class,
            DbSeedPluginCommand::class,
            DbWipePluginCommand::class,
            ConfigCachePluginCommand::class,
            RouteCachePluginCommand::class,
            ViewCachePluginCommand::class,
            EventCachePluginCommand::class,
            RouteListPluginCommand::class,
            VendorPublishPluginCommand::class,
            OptimizePluginCommand::class,
            QueueWorkPluginCommand::class,
            TestPluginCommand::class,
        ];
    }
}
