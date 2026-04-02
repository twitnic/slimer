<?php

namespace Twitnic\Slimer\Tests;

use PHPUnit\Framework\TestCase;
use Twitnic\Slimer\Project;

class ProjectTest extends TestCase
{
    protected $serverBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;

        parent::tearDown();
    }

    public function testBootstrapEnvironmentProvidesCliServerDefaults(): void
    {
        $project = new TestableProject(__DIR__);
        $keys = array(
            'REQUEST_METHOD',
            'REMOTE_ADDR',
            'REQUEST_URI',
            'SERVER_NAME',
            'HTTP_HOST',
            'SERVER_PORT',
            'SERVER_PROTOCOL',
            'QUERY_STRING',
            'HTTPS',
            'SCRIPT_FILENAME',
            'SCRIPT_NAME',
            'PHP_SELF',
            'DOCUMENT_ROOT',
        );

        foreach ($keys as $key) {
            unset($_SERVER[$key]);
        }

        $captured = null;
        $result = $project->runWithBootstrapEnvironment(__DIR__ . '/fixtures/index.php', function () use (&$captured, $keys) {
            $captured = array();

            foreach ($keys as $key) {
                $captured[$key] = $_SERVER[$key];
            }

            return 'bootstrapped';
        });

        $this->assertSame('bootstrapped', $result);
        $this->assertSame('GET', $captured['REQUEST_METHOD']);
        $this->assertSame('127.0.0.1', $captured['REMOTE_ADDR']);
        $this->assertSame('/', $captured['REQUEST_URI']);
        $this->assertSame('localhost', $captured['SERVER_NAME']);
        $this->assertSame('localhost', $captured['HTTP_HOST']);
        $this->assertSame('80', $captured['SERVER_PORT']);
        $this->assertSame('HTTP/1.1', $captured['SERVER_PROTOCOL']);
        $this->assertSame('', $captured['QUERY_STRING']);
        $this->assertSame('off', $captured['HTTPS']);
        $this->assertSame(__DIR__ . '/fixtures/index.php', $captured['SCRIPT_FILENAME']);
        $this->assertSame('/index.php', $captured['SCRIPT_NAME']);
        $this->assertSame('/index.php', $captured['PHP_SELF']);
        $this->assertSame(__DIR__ . '/fixtures', $captured['DOCUMENT_ROOT']);

        foreach ($keys as $key) {
            $this->assertArrayNotHasKey($key, $_SERVER);
        }
    }

    public function testBootstrapEnvironmentPreservesExistingServerValues(): void
    {
        $project = new TestableProject(__DIR__);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'example.test';

        $project->runWithBootstrapEnvironment(__DIR__ . '/fixtures/index.php', function () {
            $this->assertSame('POST', $_SERVER['REQUEST_METHOD']);
            $this->assertSame('example.test', $_SERVER['SERVER_NAME']);
        });

        $this->assertSame('POST', $_SERVER['REQUEST_METHOD']);
        $this->assertSame('example.test', $_SERVER['SERVER_NAME']);
    }
}

class TestableProject extends Project
{
    public function runWithBootstrapEnvironment($path, $callback)
    {
        return $this->withBootstrapEnvironment($path, $callback);
    }
}
