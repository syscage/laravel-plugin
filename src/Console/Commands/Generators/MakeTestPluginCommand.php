<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\TestMakeCommand;
use Illuminate\Support\Str;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

final class MakeTestPluginCommand extends TestMakeCommand
{
    use ResolvesTargetPlugin;

    protected $name = 'make:test-plugin';

    protected $description = 'Create a new test class inside a plugin';

    protected function rootNamespace()
    {
        return rtrim($this->targetPlugin()->namespace(), '\\') . '\\Tests';
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->targetPlugin()->path('tests') . str_replace('\\', '/', $name) . '.php';
    }
}
