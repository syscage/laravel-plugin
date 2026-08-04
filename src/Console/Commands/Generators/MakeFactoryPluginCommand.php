<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Support\Stringable;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

final class MakeFactoryPluginCommand extends FactoryMakeCommand
{
    use ResolvesTargetPlugin;

    protected $name = 'make:factory-plugin';

    protected $description = 'Create a new model factory inside a plugin';

    protected function rootNamespace()
    {
        return rtrim($this->targetPlugin()->namespace(), '\\') . '\\';
    }

    protected function getPath($name)
    {
        $name = (new Stringable($name))->replaceFirst($this->rootNamespace(), '')->finish('Factory')->value();

        return $this->targetPlugin()->factoryPath(str_replace('\\', '/', $name) . '.php');
    }
}
