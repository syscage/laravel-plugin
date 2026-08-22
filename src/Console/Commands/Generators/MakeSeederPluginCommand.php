<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Support\Str;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

final class MakeSeederPluginCommand extends SeederMakeCommand
{
    use ResolvesTargetPlugin;

    protected $name = 'make:seeder-plugin';

    protected $description = 'Create a new seeder class inside a plugin';

    protected function rootNamespace()
    {
        return rtrim($this->targetPlugin()->namespace(), '\\') . '\\Database\\Seeders\\';
    }

    protected function getPath($name)
    {
        $name = str_replace('\\', '/', Str::replaceFirst($this->rootNamespace(), '', $name));

        return $this->targetPlugin()->seederPath($name . '.php');
    }
}
