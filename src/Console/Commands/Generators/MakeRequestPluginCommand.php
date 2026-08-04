<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\RequestMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeRequestPluginCommand extends RequestMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:request-plugin';

    protected $description = 'Create a new form request class inside a plugin';
}
