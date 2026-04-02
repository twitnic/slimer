<?php

namespace Twitnic\Slimer\Tests;

use PHPUnit\Framework\TestCase;
use Twitnic\Slimer\Filesystem;
use Twitnic\Slimer\Generator\StubGenerator;

class StubGeneratorTest extends TestCase
{
    protected $temporaryDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = sys_get_temp_dir() . '/slimer-stub-' . uniqid('', true);
        mkdir($this->temporaryDirectory . '/stubs', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->temporaryDirectory);

        parent::tearDown();
    }

    public function testStubGeneratorReplacesTokens()
    {
        file_put_contents($this->temporaryDirectory . '/stubs/example.stub', "Hello DummyName\n");

        $generator = new StubGenerator(new Filesystem(), $this->temporaryDirectory . '/stubs');
        $path = $generator->generate(
            $this->temporaryDirectory . '/output/example.txt',
            'example.stub',
            array('DummyName' => 'World'),
            true
        );

        $this->assertSame($this->temporaryDirectory . '/output/example.txt', $path);
        $this->assertSame("Hello World\n", file_get_contents($path));
    }

    protected function deleteDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
