<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Tests\Support\FakeDashboardWidget;
use Syscage\Plugin\Widgets\DashboardWidgetAuthorization;

final class DashboardWidgetAuthorizationTest extends TestCase
{
    private function makeGate(bool $allows): Gate
    {
        $gate = $this->createStub(Gate::class);
        $gate->method('forUser')->willReturn($gate);
        $gate->method('allows')->willReturn($allows);

        return $gate;
    }

    private function makeUser(): Authenticatable
    {
        return $this->createStub(Authenticatable::class);
    }

    public function test_it_authorizes_a_widget_with_no_restrictions(): void
    {
        $authorization = new DashboardWidgetAuthorization($this->makeGate(true));

        $this->assertTrue($authorization->authorize(new FakeDashboardWidget(), $this->makeUser()));
    }

    public function test_it_denies_a_widget_when_the_permission_check_fails(): void
    {
        $authorization = new DashboardWidgetAuthorization($this->makeGate(false));
        $widget = new FakeDashboardWidget(permissionValue: 'manage-demo');

        $this->assertFalse($authorization->authorize($widget, $this->makeUser()));
    }

    public function test_it_denies_a_role_restricted_widget_when_no_role_system_is_resolvable(): void
    {
        $authorization = new DashboardWidgetAuthorization($this->makeGate(true));
        $widget = new FakeDashboardWidget(rolesValue: ['admin']);

        $this->assertFalse($authorization->authorize($widget, $this->makeUser()));
    }

    public function test_it_uses_the_configured_role_resolver(): void
    {
        $authorization = new DashboardWidgetAuthorization(
            $this->makeGate(true),
            static fn (Authenticatable $user, array $roles): bool => in_array('admin', $roles, true),
        );
        $widget = new FakeDashboardWidget(rolesValue: ['admin']);

        $this->assertTrue($authorization->authorize($widget, $this->makeUser()));
    }

    public function test_it_falls_back_to_has_any_role_on_the_user_model(): void
    {
        $authorization = new DashboardWidgetAuthorization($this->makeGate(true));
        $widget = new FakeDashboardWidget(rolesValue: ['admin']);

        $user = new class implements Authenticatable {
            public function getAuthIdentifierName() { return 'id'; }
            public function getAuthIdentifier() { return 1; }
            public function getAuthPasswordName() { return 'password'; }
            public function getAuthPassword() { return ''; }
            public function getRememberToken() { return null; }
            public function setRememberToken($value) {}
            public function getRememberTokenName() { return ''; }

            public function hasAnyRole(array $roles): bool
            {
                return in_array('admin', $roles, true);
            }
        };

        $this->assertTrue($authorization->authorize($widget, $user));
    }
}
