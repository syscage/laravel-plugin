<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\JobMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeJobPluginCommand extends JobMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:job-plugin';

    protected $description = 'Create a new job class inside a plugin';
}
