<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Syscage\Plugin\Contracts\DashboardWidgetRepositoryInterface;
use Syscage\Plugin\Models\DashboardWidgetRecord;

/**
 * Eloquent-backed implementation of {@see DashboardWidgetRepositoryInterface},
 * querying whichever model class the host application has configured under
 * "plugin.widgets.model" (defaulting to {@see DashboardWidgetRecord} itself).
 */
final class DashboardWidgetRepository implements DashboardWidgetRepositoryInterface
{
    public function forUser(int|string $userId): Collection
    {
        return $this->newQuery()->where('user_id', $userId)->orderBy('position')->get();
    }

    public function findForUser(int|string $userId, string $widgetId): ?DashboardWidgetRecord
    {
        return $this->newQuery()->where('user_id', $userId)->where('widget_id', $widgetId)->first();
    }

    public function maxPositionForUser(int|string $userId): int
    {
        $max = $this->newQuery()->where('user_id', $userId)->max('position');

        return $max === null ? -1 : (int) $max;
    }

    public function upsert(int|string $userId, string $widgetId, array $attributes): DashboardWidgetRecord
    {
        $record = $this->findForUser($userId, $widgetId);

        if ($record === null) {
            return $this->newQuery()->create([
                'user_id' => $userId,
                'widget_id' => $widgetId,
                ...$attributes,
            ]);
        }

        $record->fill($attributes)->save();

        return $record;
    }

    public function delete(DashboardWidgetRecord $record): void
    {
        $record->delete();
    }

    public function deleteForUser(int|string $userId): void
    {
        $this->newQuery()->where('user_id', $userId)->delete();
    }

    /**
     * @return Builder<DashboardWidgetRecord>
     */
    private function newQuery(): Builder
    {
        $model = $this->modelClass();

        return (new $model())->newQuery();
    }

    /**
     * @return class-string<DashboardWidgetRecord>
     */
    private function modelClass(): string
    {
        return (string) config('plugin.widgets.model', DashboardWidgetRecord::class);
    }
}
