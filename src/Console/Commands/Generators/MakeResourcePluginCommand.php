<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\ResourceMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeResourcePluginCommand extends ResourceMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:resource-plugin';

    protected $description = 'Create a new resource inside a plugin';
}
