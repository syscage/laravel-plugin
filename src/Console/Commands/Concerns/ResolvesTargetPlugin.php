<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Concerns;

use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;

/**
 * Prepends a required "plugin" argument to a generator command and resolves
 * it to a {@see PluginInterface}, accepting either the plugin's alias
 * (e.g. "my-plugin") or its manifest name (e.g. "MyPlugin").
 */
trait ResolvesTargetPlugin
{
    private ?PluginInterface $resolvedTargetPlugin = null;

    protected function getArguments()
    {
        return [
            ['plugin', InputArgument::REQUIRED, 'The plugin to generate into'],
            ...parent::getArguments(),
        ];
    }

    protected function targetPlugin(): PluginInterface
    {
        if ($this->resolvedTargetPlugin !== null) {
            return $this->resolvedTargetPlugin;
        }

        $manager = $this->laravel->make(PluginManagerInterface::class);
        $input = (string) $this->argument('plugin');

        if ($manager->has($input)) {
            return $this->resolvedTargetPlugin = $manager->find($input);
        }

        $alias = Str::kebab($input);

        if ($manager->has($alias)) {
            return $this->resolvedTargetPlugin = $manager->find($alias);
        }

        throw new RuntimeException("No plugin registered with the alias [{$input}].");
    }

    /**
     * Prefix a table name with the target plugin's table prefix (its own
     * "table_prefix" manifest override, or the framework's globally
     * configured default), unless it is already prefixed.
     */
    protected function prefixedPluginTable(string $table): string
    {
        $prefix = $this->targetPlugin()->tablePrefix() ?? (string) config('plugin.table_prefix', 'plugin_');

        if ($prefix === '' || Str::startsWith($table, $prefix)) {
            return $table;
        }

        return $prefix . $table;
    }
}
