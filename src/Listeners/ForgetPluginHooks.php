<?php

declare(strict_types=1);

namespace Syscage\Plugin\Listeners;

use Syscage\Plugin\Contracts\HookManagerInterface;
use Syscage\Plugin\Events\PluginDisabled;
use Syscage\Plugin\Events\PluginUninstalled;

/**
 * Drops every hook a plugin registered once it is disabled or uninstalled.
 */
final class ForgetPluginHooks
{
    public function __construct(private readonly HookManagerInterface $hooks)
    {
    }

    /**
     * Handle a plugin being disabled.
     */
    public function handleDisabled(PluginDisabled $event): void
    {
        $this->hooks->forget($event->plugin->alias());
    }

    /**
     * Handle a plugin being uninstalled.
     */
    public function handleUninstalled(PluginUninstalled $event): void
    {
        $this->hooks->forget($event->plugin->alias());
    }
}
