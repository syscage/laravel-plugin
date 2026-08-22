<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Dispatched immediately before a plugin is updated.
 */
final class PluginUpdating extends PluginEvent
{
    public function __construct(
        PluginInterface $plugin,
        public readonly string $oldVersion,
    ) {
        parent::__construct($plugin);
    }
}
