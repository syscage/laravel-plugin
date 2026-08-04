<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

/**
 * Redirects "make:model-plugin" and every companion class it can scaffold
 * (factory, migration, seeder, controller, form requests, policy) into the
 * target plugin, by delegating to the "*-plugin" alias of each companion
 * generator instead of Laravel's host-app-rooted originals.
 */
final class MakeModelPluginCommand extends ModelMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:model-plugin';

    protected $description = 'Create a new Eloquent model class inside a plugin';

    /**
     * Build the class with the given name, adding an explicit "$table"
     * property so the generated model targets its plugin-prefixed table
     * regardless of Eloquent's default table-naming convention.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        $property = "    protected \$table = '{$this->tableName()}';\n\n";

        return preg_replace('/^(class\s+\S+\s+extends\s+\S+\s*\r?\n\{\r?\n)/m', '$1' . $property, $class, 1);
    }

    /**
     * Create a model factory for the model inside the target plugin.
     *
     * @return void
     */
    protected function createFactory()
    {
        $factory = Str::studly($this->argument('name'));

        $this->call('make:factory-plugin', [
            'plugin' => $this->argument('plugin'),
            'name' => "{$factory}Factory",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * Create a migration file for the model inside the target plugin.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = $this->tableName();

        $this->call('make:migration-plugin', [
            'plugin' => $this->argument('plugin'),
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    /**
     * Create a seeder file for the model inside the target plugin.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $seeder = Str::studly(class_basename($this->argument('name')));

        $this->call('make:seeder-plugin', [
            'plugin' => $this->argument('plugin'),
            'name' => "{$seeder}Seeder",
        ]);
    }

    /**
     * Create a controller for the model inside the target plugin.
     *
     * @return void
     */
    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('make:controller-plugin', array_filter([
            'plugin' => $this->argument('plugin'),
            'name' => "{$controller}Controller",
            '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
            '--api' => $this->option('api'),
            '--requests' => $this->option('requests') || $this->option('all'),
            '--test' => $this->option('test'),
            '--pest' => $this->option('pest'),
        ]));
    }

    /**
     * Create the form requests for the model inside the target plugin.
     *
     * @return void
     */
    protected function createFormRequests()
    {
        $request = Str::studly(class_basename($this->argument('name')));

        $this->call('make:request-plugin', [
            'plugin' => $this->argument('plugin'),
            'name' => "Store{$request}Request",
        ]);

        $this->call('make:request-plugin', [
            'plugin' => $this->argument('plugin'),
            'name' => "Update{$request}Request",
        ]);
    }

    /**
     * Create a policy file for the model inside the target plugin.
     *
     * @return void
     */
    protected function createPolicy()
    {
        $policy = Str::studly(class_basename($this->argument('name')));

        $this->call('make:policy-plugin', [
            'plugin' => $this->argument('plugin'),
            'name' => "{$policy}Policy",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * The plugin-prefixed table name this model's migration creates.
     */
    private function tableName(): string
    {
        return $this->prefixedPluginTable($this->baseTableName());
    }

    /**
     * The unprefixed table name derived from the model's class name.
     */
    private function baseTableName(): string
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        return $this->option('pivot') ? Str::singular($table) : $table;
    }
}
