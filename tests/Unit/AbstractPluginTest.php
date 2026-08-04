<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\AbstractPlugin;
use Syscage\Plugin\Contracts\PluginManifestInterface;

final class AbstractPluginTest extends TestCase
{
    private function makeManifest(): PluginManifestInterface
    {
        return new class implements PluginManifestInterface {
            public function id(): ?string
            {
                return '1ca65e6b-51dc-40e8-a272-964f5d3cd5a0';
            }

            public function name(): string
            {
                return 'MyPlugin';
            }

            public function alias(): string
            {
                return 'my-plugin';
            }

            public function description(): string
            {
                return 'MyPlugin Integration';
            }

            public function version(): string
            {
                return '1.0.0';
            }

            public function provider(): string
            {
                return 'Plugins\\MyPlugin\\Providers\\MyPluginServiceProvider';
            }

            public function namespace(): string
            {
                return 'Plugins\\MyPlugin';
            }

            public function priority(): int
            {
                return 100;
            }

            public function enabled(): bool
            {
                return true;
            }

            public function requires(): array
            {
                return [];
            }

            public function conflicts(): array
            {
                return [];
            }

            public function permissions(): array
            {
                return [];
            }

            public function sidebar(): array
            {
                return [];
            }

            public function authors(): array
            {
                return [];
            }

            public function tablePrefix(): ?string
            {
                return null;
            }

            public function toArray(): array
            {
                return [];
            }
        };
    }

    public function test_it_delegates_manifest_accessors(): void
    {
        $plugin = new class ($this->makeManifest(), '/app/plugins/my-plugin') extends AbstractPlugin {
        };

        $this->assertSame('MyPlugin', $plugin->name());
        $this->assertSame('my-plugin', $plugin->alias());
        $this->assertTrue($plugin->enabled());
        $this->assertSame(100, $plugin->priority());
    }

    public function test_it_computes_paths_from_the_standard_layout(): void
    {
        $plugin = new class ($this->makeManifest(), '/app/plugins/my-plugin') extends AbstractPlugin {
        };

        $this->assertSame('/app/plugins/my-plugin', $plugin->path());
        $this->assertSame('/app/plugins/my-plugin' . DIRECTORY_SEPARATOR . 'src', $plugin->srcPath());
        $this->assertSame(
            '/app/plugins/my-plugin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'app',
            $plugin->appPath(),
        );
        $this->assertSame(
            implode(DIRECTORY_SEPARATOR, ['/app/plugins/my-plugin', 'src', 'database', 'migrations']),
            $plugin->migrationPath(),
        );
        $this->assertSame(
            implode(DIRECTORY_SEPARATOR, ['/app/plugins/my-plugin', 'src', 'resources', 'views']),
            $plugin->viewPath(),
        );
        $this->assertSame(
            implode(DIRECTORY_SEPARATOR, ['/app/plugins/my-plugin', 'src', 'database', 'migrations', 'foo.php']),
            $plugin->migrationPath('foo.php'),
        );
    }

    public function test_default_lifecycle_hooks_are_no_ops(): void
    {
        $plugin = new class ($this->makeManifest(), '/app/plugins/my-plugin') extends AbstractPlugin {
        };

        $plugin->install();
        $plugin->uninstall();
        $plugin->enable();
        $plugin->disable();
        $plugin->update('0.9.0');

        $this->addToAssertionCount(5);
    }
}
