<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

/**
 * Dispatched before a user's dashboard layout is built.
 */
final class DashboardBuilding
{
    public function __construct(
        public readonly int|string $userId,
    ) {
    }
}
