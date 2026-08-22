<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Syscage\Plugin\Contracts\DashboardWidgetAuthorizationInterface;
use Syscage\Plugin\Contracts\DashboardWidgetInterface;

/**
 * Default implementation of {@see DashboardWidgetAuthorizationInterface}.
 *
 * Permissions are checked through Laravel's own {@see Gate}. Roles have no
 * built-in Laravel concept, so a role check is delegated to an optional
 * resolver callback (configured via "plugin.widgets.role_resolver"); when a
 * widget declares roles but no resolver is configured and the user model
 * exposes no recognizable role-checking method, the widget is treated as
 * unauthorized rather than silently ignoring the restriction.
 */
final class DashboardWidgetAuthorization implements DashboardWidgetAuthorizationInterface
{
    public function __construct(
        private readonly Gate $gate,
        private readonly ?Closure $roleResolver = null,
    ) {
    }

    public function authorize(DashboardWidgetInterface $widget, Authenticatable $user): bool
    {
        if ($widget->permission() !== null && ! $this->gate->forUser($user)->allows($widget->permission())) {
            return false;
        }

        if ($widget->roles() !== [] && ! $this->userHasAnyRole($user, $widget->roles())) {
            return false;
        }

        return true;
    }

    /**
     * @param array<int, string> $roles
     */
    private function userHasAnyRole(Authenticatable $user, array $roles): bool
    {
        if ($this->roleResolver !== null) {
            return (bool) ($this->roleResolver)($user, $roles);
        }

        if (method_exists($user, 'hasAnyRole')) {
            return (bool) $user->hasAnyRole($roles);
        }

        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
}
