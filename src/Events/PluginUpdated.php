<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Dispatched after a plugin has been updated.
 */
final class PluginUpdated extends PluginEvent
{
    public function __construct(
        PluginInterface $plugin,
        public readonly string $oldVersion,
    ) {
        parent::__construct($plugin);
    }
}
