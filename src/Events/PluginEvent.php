<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Base class for every plugin lifecycle event.
 */
abstract class PluginEvent
{
    public function __construct(
        public readonly PluginInterface $plugin,
    ) {
    }
}
