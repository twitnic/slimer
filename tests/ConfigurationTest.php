<?php

namespace Twitnic\Slimer\Tests;

use PHPUnit\Framework\TestCase;
use Twitnic\Slimer\Config\Configuration;
use Twitnic\Slimer\Filesystem;

class ConfigurationTest extends TestCase
{
    protected $temporaryDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = sys_get_temp_dir() . '/slimer-config-' . uniqid('', true);
        mkdir($this->temporaryDirectory, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->temporaryDirectory);

        parent::tearDown();
    }

    public function testConfigurationIsLoadedFromDefaultLocation()
    {
        file_put_contents(
            $this->temporaryDirectory . '/.slimer.php',
            "<?php\nreturn array('bootstrap' => 'public/index.php', 'commands' => array('DemoCommand'));\n"
        );

        $configuration = Configuration::fromDirectory($this->temporaryDirectory, new Filesystem());

        $this->assertTrue($configuration->exists());
        $this->assertSame('public/index.php', $configuration->bootstrap());
        $this->assertSame(array('DemoCommand'), $configuration->commands());
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
