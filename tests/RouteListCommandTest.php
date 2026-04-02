<?php

namespace Twitnic\Slimer\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Twitnic\Slimer\Command\RouteListCommand;
use Twitnic\Slimer\Project;

class RouteListCommandTest extends TestCase
{
    public function testRouteListHandlesTraversableNamedRoutes(): void
    {
        $route = new FakeRoute(array('GET'), '/status', 'status', 'StatusAction');
        $router = new FakeNamedRoutesRouter(new ArrayObject(array('status' => $route)));
        $application = new FakeApplication($router);
        $command = new RouteListCommand(new FakeProject(__DIR__, $application));
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(array());
        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('GET', $display);
        $this->assertStringContainsString('/status', $display);
        $this->assertStringContainsString('status', $display);
        $this->assertStringContainsString('StatusAction', $display);
    }

    public function testRouteListHandlesSingleNamedRouteObject(): void
    {
        $route = new FakeRoute(array('POST'), '/jobs', 'jobs', 'JobsAction');
        $router = new FakeNamedRoutesRouter($route);
        $application = new FakeApplication($router);
        $command = new RouteListCommand(new FakeProject(__DIR__, $application));
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(array());
        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('POST', $display);
        $this->assertStringContainsString('/jobs', $display);
        $this->assertStringContainsString('jobs', $display);
        $this->assertStringContainsString('JobsAction', $display);
    }
}

class FakeProject extends Project
{
    protected $application;

    public function __construct($workingDirectory, $application)
    {
        parent::__construct($workingDirectory);

        $this->application = $application;
    }

    public function bootstrap()
    {
        return $this->application;
    }
}

class FakeApplication
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function router()
    {
        return $this->router;
    }
}

class FakeNamedRoutesRouter
{
    protected $namedRoutes;

    public function __construct($namedRoutes)
    {
        $this->namedRoutes = $namedRoutes;
    }

    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }
}

class FakeRoute
{
    protected $methods;
    protected $pattern;
    protected $name;
    protected $callable;

    public function __construct(array $methods, $pattern, $name, $callable)
    {
        $this->methods = $methods;
        $this->pattern = $pattern;
        $this->name = $name;
        $this->callable = $callable;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCallable()
    {
        return $this->callable;
    }
}
