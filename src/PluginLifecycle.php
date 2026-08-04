<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Contracts\Events\Dispatcher;
use Syscage\Plugin\Contracts\PluginDependencyResolverInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginManifestRepositoryInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Contracts\PluginRegistryInterface;
use Syscage\Plugin\Events\PluginDisabled;
use Syscage\Plugin\Events\PluginDisabling;
use Syscage\Plugin\Events\PluginEnabled;
use Syscage\Plugin\Events\PluginEnabling;
use Syscage\Plugin\Events\PluginInstalled;
use Syscage\Plugin\Events\PluginInstalling;
use Syscage\Plugin\Events\PluginUninstalled;
use Syscage\Plugin\Events\PluginUninstalling;
use Syscage\Plugin\Events\PluginUpdated;
use Syscage\Plugin\Events\PluginUpdating;
use Syscage\Plugin\Exceptions\PluginAlreadyInstalledException;
use Syscage\Plugin\Exceptions\PluginNotInstalledException;
use Syscage\Plugin\Models\PluginRecord;
use Syscage\Plugin\Support\PluginHasher;
use Syscage\Plugin\Support\PluginManifest;

/**
 * Default implementation of {@see PluginLifecycleInterface}.
 */
final class PluginLifecycle implements PluginLifecycleInterface
{
    public function __construct(
        private readonly PluginRecordRepositoryInterface $records,
        private readonly PluginManifestRepositoryInterface $manifests,
        private readonly PluginDependencyResolverInterface $resolver,
        private readonly PluginRegistryInterface $registry,
        private readonly Dispatcher $events,
        private readonly string $manifestFilename,
    ) {
    }

    public function install(PluginInterface $plugin): void
    {
        if ($this->records->exists($plugin->alias())) {
            throw PluginAlreadyInstalledException::forAlias($plugin->alias());
        }

        $this->resolver->validate($plugin, $this->registry->all());

        $this->events->dispatch(new PluginInstalling($plugin));

        $record = $this->records->create([
            'name' => $plugin->name(),
            'alias' => $plugin->alias(),
            'description' => $plugin->description(),
            'version' => $plugin->version(),
            'provider' => $plugin->provider(),
            'namespace' => $plugin->namespace(),
            'priority' => $plugin->priority(),
            'enabled' => $plugin->enabled(),
            'installed_at' => now(),
            'enabled_at' => $plugin->enabled() ? now() : null,
            'path' => $plugin->path(),
            'hash' => PluginHasher::hash($plugin),
        ]);

        $this->syncManifest($plugin, $record);

        $plugin->install();

        $this->events->dispatch(new PluginInstalled($plugin));
    }

    public function uninstall(PluginInterface $plugin): void
    {
        $record = $this->findRecordOrFail($plugin);

        $this->events->dispatch(new PluginUninstalling($plugin));

        $plugin->uninstall();

        $this->records->delete($record);
        $this->registry->forget($plugin->alias());

        $this->events->dispatch(new PluginUninstalled($plugin));
    }

    public function enable(PluginInterface $plugin): void
    {
        $record = $this->findRecordOrFail($plugin);

        $this->resolver->validate($plugin, $this->registry->all());

        $this->events->dispatch(new PluginEnabling($plugin));

        $this->records->update($record, [
            'enabled' => true,
            'enabled_at' => now(),
            'disabled_at' => null,
        ]);

        $this->writeManifest($plugin, fn (PluginManifest $manifest): PluginManifest => $manifest->withEnabled(true));

        $plugin->enable();

        $this->events->dispatch(new PluginEnabled($plugin));
    }

    public function disable(PluginInterface $plugin): void
    {
        $record = $this->findRecordOrFail($plugin);

        $this->events->dispatch(new PluginDisabling($plugin));

        $this->records->update($record, [
            'enabled' => false,
            'disabled_at' => now(),
        ]);

        $this->writeManifest($plugin, fn (PluginManifest $manifest): PluginManifest => $manifest->withEnabled(false));

        $plugin->disable();

        $this->events->dispatch(new PluginDisabled($plugin));
    }

    public function update(PluginInterface $plugin, string $oldVersion): void
    {
        $record = $this->findRecordOrFail($plugin);

        $this->events->dispatch(new PluginUpdating($plugin, $oldVersion));

        $this->records->update($record, [
            'version' => $plugin->version(),
            'hash' => PluginHasher::hash($plugin),
        ]);

        $plugin->update($oldVersion);

        $this->events->dispatch(new PluginUpdated($plugin, $oldVersion));
    }

    private function findRecordOrFail(PluginInterface $plugin): PluginRecord
    {
        return $this->records->findByAlias($plugin->alias())
            ?? throw PluginNotInstalledException::forAlias($plugin->alias());
    }

    private function syncManifest(PluginInterface $plugin, PluginRecord $record): void
    {
        $manifest = PluginManifest::fromArray($plugin->toArray())->withId($record->uuid);

        $this->manifests->write($this->manifestPath($plugin), $manifest);
    }

    /**
     * @param callable(PluginManifest): PluginManifest $mutate
     */
    private function writeManifest(PluginInterface $plugin, callable $mutate): void
    {
        $manifest = $mutate(PluginManifest::fromArray($plugin->toArray()));

        $this->manifests->write($this->manifestPath($plugin), $manifest);
    }

    private function manifestPath(PluginInterface $plugin): string
    {
        return $plugin->path($this->manifestFilename);
    }
}
