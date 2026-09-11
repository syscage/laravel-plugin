<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
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
}
