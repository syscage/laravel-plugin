<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Concerns;

use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
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

    /**
     * Prepend "plugin" for wrapped commands that build their definition
     * from the legacy $name + getArguments()/getOptions() pair.
     *
     * Left in place for commands such as Laravel's own
     * {@see \Illuminate\Routing\Console\ControllerMakeCommand}, which
     * still declare $name instead of a fluent $signature.
     */
    protected function getArguments()
    {
        return [
            ['plugin', InputArgument::REQUIRED, 'The plugin to generate into'],
            ...parent::getArguments(),
        ];
    }

    /**
     * Rename the wrapped command to its "-plugin" alias and splice a
     * required leading "plugin" argument onto its definition, for
     * commands that build their definition from a fluent $signature
     * instead of the legacy $name + getArguments() pair.
     *
     * A fluent $signature is parsed here, by the base command
     * constructor, before this class's own constructor runs — so the
     * getArguments() override above is never consulted, and the
     * inherited $signature silently overwrites $this->name with the
     * wrapped command's original, unsuffixed name. This is patched
     * after the fact instead of redeclaring $signature per command,
     * since duplicating each wrapped command's argument/option syntax
     * here would drift from Laravel's own definitions across versions.
     */
    protected function configureUsingFluentDefinition()
    {
        $aliasName = $this->name ?? null;

        parent::configureUsingFluentDefinition();

        if ($aliasName !== null) {
            $this->setName($aliasName);
        }

        $definition = $this->getDefinition();

        if (! $definition->hasArgument('plugin')) {
            $this->setDefinition(new InputDefinition([
                new InputArgument('plugin', InputArgument::REQUIRED, 'The plugin to generate into'),
                ...array_values($definition->getArguments()),
                ...array_values($definition->getOptions()),
            ]));
        }
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
