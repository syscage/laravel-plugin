<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Illuminate\Support\Collection;
use Syscage\Plugin\Models\DashboardWidgetRecord;

/**
 * Persists and retrieves each user's personal dashboard widget layout.
 *
 * User-specific widget data must never be stored in the global widget
 * metadata cache produced by {@see DashboardWidgetCacheInterface}.
 */
interface DashboardWidgetRepositoryInterface
{
    /**
     * All of a user's stored widget layout rows, ordered by position.
     *
     * @return Collection<int, DashboardWidgetRecord>
     */
    public function forUser(int|string $userId): Collection;

    /**
     * Find a user's stored layout row for a specific widget.
     */
    public function findForUser(int|string $userId, string $widgetId): ?DashboardWidgetRecord;

    /**
     * The highest stored position for a user's widgets, or -1 when none exist.
     */
    public function maxPositionForUser(int|string $userId): int;

    /**
     * Create or update a user's layout row for a specific widget.
     *
     * @param array<string, mixed> $attributes
     */
    public function upsert(int|string $userId, string $widgetId, array $attributes): DashboardWidgetRecord;

    /**
     * Delete a single stored layout row.
     */
    public function delete(DashboardWidgetRecord $record): void;

    /**
     * Delete every stored layout row for a user, reverting them to the
     * default dashboard layout.
     */
    public function deleteForUser(int|string $userId): void;
}
