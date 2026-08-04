<?php

declare(strict_types=1);

namespace Syscage\Plugin\Support;

use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Computes a content hash of a plugin's manifest, used to detect drift
 * between the "plugin.json" on disk and what was last installed.
 */
final class PluginHasher
{
    public static function hash(PluginInterface $plugin): string
    {
        return hash('sha256', json_encode($plugin->toArray(), JSON_THROW_ON_ERROR));
    }
}
