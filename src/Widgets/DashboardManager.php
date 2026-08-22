<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\DashboardManagerInterface;
use Syscage\Plugin\Contracts\DashboardWidgetManagerInterface;
use Syscage\Plugin\Contracts\DashboardWidgetRepositoryInterface;
use Syscage\Plugin\Events\DashboardBuilding;
use Syscage\Plugin\Events\DashboardBuilt;
use Syscage\Plugin\Events\WidgetConfigured;
use Syscage\Plugin\Events\WidgetDisabled;
use Syscage\Plugin\Events\WidgetEnabled;

/**
 * Default implementation of {@see DashboardManagerInterface}.
 */
final class DashboardManager implements DashboardManagerInterface
{
    public function __construct(
        private readonly DashboardWidgetManagerInterface $widgets,
        private readonly DashboardWidgetRepositoryInterface $repository,
        private readonly Filesystem $files,
        private readonly Dispatcher $events,
        private readonly string $cachePath,
    ) {
    }

    public function defaultLayout(): array
    {
        $position = 0;
        $layout = [];

        foreach ($this->widgets->enabled() as $widget) {
            $layout[] = [
                'widget' => $widget,
                'position' => $position++,
                'width' => $widget->defaultWidth(),
            ];
        }

        return $layout;
    }

    public function layoutFor(Authenticatable $user): array
    {
        $userId = $user->getAuthIdentifier();

        $this->events->dispatch(new DashboardBuilding($userId));

        $records = $this->repository->forUser($userId)->keyBy('widget_id');

        $entries = [];
        $fallbackPosition = 0;

        foreach ($this->widgets->availableFor($user) as $id => $widget) {
            $record = $records->get($id);

            if ($record !== null && ! $record->is_enabled) {
                continue;
            }

            $entries[] = [
                'widget' => $widget,
                'position' => $record->position ?? $fallbackPosition,
                'width' => $record->width ?? $widget->defaultWidth(),
                'height' => $record->height ?? null,
                'settings' => $record->settings ?? [],
            ];

            $fallbackPosition++;
        }

        usort($entries, static fn (array $a, array $b): int => $a['position'] <=> $b['position']);

        $this->events->dispatch(new DashboardBuilt($userId, $entries));

        return $entries;
    }

    public function addWidget(Authenticatable $user, string $widgetId): void
    {
        $widget = $this->widgets->find($widgetId);
        $userId = $user->getAuthIdentifier();

        $this->repository->upsert($userId, $widgetId, [
            'is_enabled' => true,
            'position' => $this->repository->maxPositionForUser($userId) + 1,
            'width' => $widget->defaultWidth(),
        ]);

        $this->events->dispatch(new WidgetEnabled($widget, $userId));
    }

    public function removeWidget(Authenticatable $user, string $widgetId): void
    {
        $widget = $this->widgets->find($widgetId);
        $userId = $user->getAuthIdentifier();

        $this->repository->upsert($userId, $widgetId, ['is_enabled' => false]);

        $this->events->dispatch(new WidgetDisabled($widget, $userId));
    }

    public function moveWidget(Authenticatable $user, string $widgetId, int $position): void
    {
        $this->widgets->find($widgetId);

        $this->repository->upsert($user->getAuthIdentifier(), $widgetId, ['position' => $position]);
    }

    public function resizeWidget(Authenticatable $user, string $widgetId, int $width, ?int $height = null): void
    {
        $widget = $this->widgets->find($widgetId);

        $width = max($widget->minWidth(), min($widget->maxWidth(), $width));

        $this->repository->upsert($user->getAuthIdentifier(), $widgetId, [
            'width' => $width,
            'height' => $height,
        ]);
    }

    public function configureWidget(Authenticatable $user, string $widgetId, array $settings): void
    {
        $widget = $this->widgets->find($widgetId);
        $userId = $user->getAuthIdentifier();

        $this->repository->upsert($userId, $widgetId, ['settings' => $settings]);

        $this->events->dispatch(new WidgetConfigured($widget, $userId, $settings));
    }

    public function resetLayout(Authenticatable $user): void
    {
        $this->repository->deleteForUser($user->getAuthIdentifier());
    }

    public function cacheDefaultLayout(): array
    {
        $layout = $this->defaultLayout();

        $directory = dirname($this->cachePath);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, recursive: true);
        }

        $exportable = array_map(
            static fn (array $entry): array => [
                'widget' => $entry['widget']->id(),
                'position' => $entry['position'],
                'width' => $entry['width'],
            ],
            $layout,
        );

        $export = var_export($exportable, true);

        $this->files->put($this->cachePath, "<?php\n\nreturn {$export};\n");

        return $layout;
    }

    public function forgetDefaultLayoutCache(): void
    {
        if ($this->files->isFile($this->cachePath)) {
            $this->files->delete($this->cachePath);
        }
    }
}
