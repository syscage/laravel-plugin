<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\FrontendDetector;

final class FrontendDetectorTest extends TestCase
{
    private string $packageJsonPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packageJsonPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('package-json-', true) . '.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->packageJsonPath);

        parent::tearDown();
    }

    private function writePackageJson(array $dependencies, array $devDependencies = []): void
    {
        file_put_contents($this->packageJsonPath, json_encode([
            'dependencies' => $dependencies,
            'devDependencies' => $devDependencies,
        ]));
    }

    public function test_it_honors_an_explicit_configuration_override(): void
    {
        $detector = new FrontendDetector(new Filesystem(), $this->packageJsonPath, 'inertia-vue');

        $this->assertSame('inertia-vue', $detector->detect());
    }

    public function test_it_defaults_to_blade_when_no_package_json_exists(): void
    {
        $detector = new FrontendDetector(new Filesystem(), $this->packageJsonPath, 'auto');

        $this->assertSame('blade', $detector->detect());
    }

    public function test_it_detects_react(): void
    {
        $this->writePackageJson(['react' => '^18.0']);

        $detector = new FrontendDetector(new Filesystem(), $this->packageJsonPath, 'auto');

        $this->assertSame('react', $detector->detect());
    }

    public function test_it_detects_vue(): void
    {
        $this->writePackageJson(['vue' => '^3.0']);

        $detector = new FrontendDetector(new Filesystem(), $this->packageJsonPath, 'auto');

        $this->assertSame('vue', $detector->detect());
    }

    public function test_it_prefers_inertia_react_over_plain_react(): void
    {
        $this->writePackageJson(['react' => '^18.0', '@inertiajs/react' => '^1.0']);

        $detector = new FrontendDetector(new Filesystem(), $this->packageJsonPath, 'auto');

        $this->assertSame('inertia-react', $detector->detect());
    }

    public function test_it_prefers_inertia_vue_over_plain_vue(): void
    {
        $this->writePackageJson(['vue' => '^3.0', '@inertiajs/vue3' => '^1.0']);

        $detector = new FrontendDetector(new Filesystem(), $this->packageJsonPath, 'auto');

        $this->assertSame('inertia-vue', $detector->detect());
    }

    public function test_it_checks_dev_dependencies_too(): void
    {
        $this->writePackageJson([], ['vue' => '^3.0']);

        $detector = new FrontendDetector(new Filesystem(), $this->packageJsonPath, 'auto');

        $this->assertSame('vue', $detector->detect());
    }
}
