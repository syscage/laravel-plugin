<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\ProviderMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeProviderPluginCommand extends ProviderMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:provider-plugin';

    protected $description = 'Create a new service provider class inside a plugin';
}
