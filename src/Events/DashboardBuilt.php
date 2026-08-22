<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

/**
 * Dispatched after a user's dashboard layout has been built.
 */
final class DashboardBuilt
{
    /**
     * @param array<int, array<string, mixed>> $layout
     */
    public function __construct(
        public readonly int|string $userId,
        public readonly array $layout,
    ) {
    }
}
