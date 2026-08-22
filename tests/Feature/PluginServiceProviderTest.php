<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Syscage\Plugin\Contracts\DashboardManagerInterface;
use Syscage\Plugin\Contracts\DashboardWidgetAuthorizationInterface;
use Syscage\Plugin\Contracts\DashboardWidgetCacheInterface;
use Syscage\Plugin\Contracts\DashboardWidgetDiscoveryInterface;
use Syscage\Plugin\Contracts\DashboardWidgetManagerInterface;
use Syscage\Plugin\Contracts\DashboardWidgetRegistryInterface;
use Syscage\Plugin\Contracts\DashboardWidgetRepositoryInterface;
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
use Syscage\Plugin\Contracts\WidgetRendererInterface;
use Syscage\Plugin\Tests\TestCase;

final class PluginServiceProviderTest extends TestCase
{
    /**
     * @return array<int, class-string>
     */
    public static function contractProvider(): array
    {
        return [
            [PluginAutoloaderInterface::class],
            [PluginManifestRepositoryInterface::class],
            [PluginRegistryInterface::class],
            [PluginCacheInterface::class],
            [PluginDiscoveryInterface::class],
            [PluginDependencyResolverInterface::class],
            [PluginLoaderInterface::class],
            [PluginManagerInterface::class],
            [PluginRecordRepositoryInterface::class],
            [PluginLifecycleInterface::class],
            [PluginSidebarManagerInterface::class],
            [PluginRouteManagerInterface::class],
            [PluginViewManagerInterface::class],
            [PluginTranslationManagerInterface::class],
            [PluginMigrationManagerInterface::class],
            [PluginConfigManagerInterface::class],
            [PluginAssetManagerInterface::class],
            [PluginCommandManagerInterface::class],
            [DashboardWidgetRegistryInterface::class],
            [DashboardWidgetRepositoryInterface::class],
            [DashboardWidgetCacheInterface::class],
            [DashboardWidgetDiscoveryInterface::class],
            [DashboardWidgetAuthorizationInterface::class],
            [DashboardWidgetManagerInterface::class],
            [DashboardManagerInterface::class],
            [WidgetRendererInterface::class],
        ];
    }

    #[DataProvider('contractProvider')]
    public function test_every_contract_resolves_from_the_container(string $contract): void
    {
        $this->assertInstanceOf($contract, $this->app->make($contract));
    }

    public function test_it_is_bound_as_a_singleton(): void
    {
        $this->assertSame(
            $this->app->make(PluginRegistryInterface::class),
            $this->app->make(PluginRegistryInterface::class),
        );
    }

    /**
     * Reproduces a host application that published "config/plugin.php" from
     * an older version of the package: its "cache" array exists but has no
     * "widgets"/"dashboard" keys, and it has no top-level "widgets" section
     * at all. Laravel's own `mergeConfigFrom()` only merges one level deep,
     * so a top-level key already present in the host's config (like
     * "cache") is kept wholesale rather than merged key-by-key with the
     * package's defaults — every binding must therefore supply its own
     * fallback default rather than assuming the published config is current.
     */
    public function test_widget_bindings_fall_back_to_defaults_when_the_published_config_predates_them(): void
    {
        config([
            'plugin.cache' => [
                'plugins' => base_path('bootstrap/cache/plugins.php'),
                'sidebar' => base_path('bootstrap/cache/sidebar.php'),
                'frontend' => base_path('bootstrap/cache/plugins.ts'),
            ],
        ]);
        config()->offsetUnset('plugin.widgets');

        // These singletons were already resolved (and cached) during this
        // test's own application boot, using the untouched config from
        // defineEnvironment() — force them to re-resolve against the
        // config mutated above instead of returning the earlier instance.
        $this->app->forgetInstance(DashboardWidgetCacheInterface::class);
        $this->app->forgetInstance(DashboardManagerInterface::class);
        $this->app->forgetInstance(DashboardWidgetAuthorizationInterface::class);

        $this->assertInstanceOf(DashboardWidgetCacheInterface::class, $this->app->make(DashboardWidgetCacheInterface::class));
        $this->assertInstanceOf(DashboardManagerInterface::class, $this->app->make(DashboardManagerInterface::class));
        $this->assertInstanceOf(DashboardWidgetAuthorizationInterface::class, $this->app->make(DashboardWidgetAuthorizationInterface::class));
    }
}
