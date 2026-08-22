<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

/**
 * The presentation types a dashboard widget may declare.
 */
enum DashboardWidgetType: string
{
    case Stat = 'stat';
    case Chart = 'chart';
    case Table = 'table';
    case RecordList = 'list';
    case Activity = 'activity';
    case Progress = 'progress';
    case Custom = 'custom';
}
