<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\NotificationMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeNotificationPluginCommand extends NotificationMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:notification-plugin';

    protected $description = 'Create a new notification class inside a plugin';
}
