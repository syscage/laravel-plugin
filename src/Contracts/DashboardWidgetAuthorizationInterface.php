<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Determines whether a user may see a given dashboard widget.
 *
 * Permission and role checks must always happen on the backend: frontend
 * visibility alone is never sufficient.
 */
interface DashboardWidgetAuthorizationInterface
{
    /**
     * Determine whether the given user satisfies the widget's permission
     * and role restrictions.
     */
    public function authorize(DashboardWidgetInterface $widget, Authenticatable $user): bool;
}
