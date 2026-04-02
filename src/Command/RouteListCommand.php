<?php

namespace Twitnic\Slimer\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouteListCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('route:list')
            ->setDescription('List registered Slim 2 routes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->requireSlimApplication();
        $router = method_exists($application, 'router') ? $application->router() : null;
        $routes = $this->resolveRoutes($router);

        if (empty($routes)) {
            $output->writeln('<comment>No routes were registered.</comment>');
            return 0;
        }

        $rows = array();

        foreach ($routes as $route) {
            $rows[] = array(
                $this->formatMethods($route),
                method_exists($route, 'getPattern') ? $route->getPattern() : 'n/a',
                method_exists($route, 'getName') ? ($route->getName() ?: '-') : '-',
                $this->formatCallable($route),
            );
        }

        $table = new Table($output);
        $table->setHeaders(array('Method', 'Pattern', 'Name', 'Callable'));
        $table->setRows($rows);
        $table->render();

        return 0;
    }

    protected function resolveRoutes($router)
    {
        if ($router === null) {
            return array();
        }

        if (method_exists($router, 'getRoutes')) {
            return $this->normalizeRoutes($router->getRoutes());
        }

        if (method_exists($router, 'getNamedRoutes')) {
            return $this->normalizeRoutes($router->getNamedRoutes());
        }

        return array();
    }

    protected function normalizeRoutes($routes)
    {
        if ($routes === null) {
            return array();
        }

        if (is_array($routes)) {
            return array_values($routes);
        }

        if ($routes instanceof \Traversable) {
            return array_values(iterator_to_array($routes));
        }

        if (is_object($routes)) {
            return array($routes);
        }

        return array();
    }

    protected function formatMethods($route)
    {
        if (!method_exists($route, 'getMethods')) {
            return 'ANY';
        }

        $methods = $route->getMethods();

        return empty($methods) ? 'ANY' : implode('|', $methods);
    }

    protected function formatCallable($route)
    {
        if (!method_exists($route, 'getCallable')) {
            return 'n/a';
        }

        $callable = $route->getCallable();

        if (is_string($callable)) {
            return $callable;
        }

        if (is_array($callable)) {
            return implode('@', array_map(array($this, 'stringifyCallableSegment'), $callable));
        }

        if ($callable instanceof \Closure) {
            return 'Closure';
        }

        if (is_object($callable)) {
            return get_class($callable);
        }

        return 'n/a';
    }

    protected function stringifyCallableSegment($segment)
    {
        return is_object($segment) ? get_class($segment) : (string) $segment;
    }
}
