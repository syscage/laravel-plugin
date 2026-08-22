<?php

declare(strict_types=1);

namespace Syscage\Plugin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for the "dashboard_widgets" table, storing a single user's
 * per-widget layout customization (position, size, settings, visibility).
 *
 * @property int $id
 * @property int|string $user_id
 * @property string $widget_id
 * @property int $position
 * @property int|null $width
 * @property int|null $height
 * @property array<string, mixed> $settings
 * @property bool $is_enabled
 */
class DashboardWidgetRecord extends Model
{
    protected $fillable = [
        'user_id',
        'widget_id',
        'position',
        'width',
        'height',
        'settings',
        'is_enabled',
    ];

    protected $casts = [
        'position' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'settings' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function getTable(): string
    {
        return config('plugin.widgets.table', 'dashboard_widgets');
    }
}
