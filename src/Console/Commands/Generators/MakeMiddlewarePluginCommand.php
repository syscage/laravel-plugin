<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Routing\Console\MiddlewareMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeMiddlewarePluginCommand extends MiddlewareMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:middleware-plugin';

    protected $description = 'Create a new HTTP middleware class inside a plugin';
}
