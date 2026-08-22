<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Syscage\Plugin\Support\StubRenderer;

final class StubRendererTest extends TestCase
{
    private string $stubsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stubsPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stubs-', true);
        mkdir($this->stubsPath, recursive: true);
        file_put_contents($this->stubsPath . '/example.stub', "namespace {{ namespace }};\nclass {{ class }} {}\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->stubsPath . '/example.stub');
        @rmdir($this->stubsPath);

        parent::tearDown();
    }

    public function test_it_replaces_placeholder_tokens(): void
    {
        $renderer = new StubRenderer(new Filesystem(), $this->stubsPath);

        $result = $renderer->render('example.stub', [
            'namespace' => 'Plugins\\MyPlugin',
            'class' => 'MyClass',
        ]);

        $this->assertSame("namespace Plugins\\MyPlugin;\nclass MyClass {}\n", $result);
    }

    public function test_it_throws_when_the_stub_does_not_exist(): void
    {
        $renderer = new StubRenderer(new Filesystem(), $this->stubsPath);

        $this->expectException(RuntimeException::class);

        $renderer->render('missing.stub', []);
    }
}
