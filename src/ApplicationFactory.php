<?php

namespace Twitnic\Slimer;

use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Twitnic\Slimer\Command\AboutCommand;
use Twitnic\Slimer\Command\InitCommand;
use Twitnic\Slimer\Command\MakeCommandCommand;
use Twitnic\Slimer\Command\MakeControllerCommand;
use Twitnic\Slimer\Command\MakeMiddlewareCommand;
use Twitnic\Slimer\Command\MakeViewCommand;
use Twitnic\Slimer\Command\RouteListCommand;
use Twitnic\Slimer\Command\ServeCommand;

class ApplicationFactory
{
    public static function create(Project $project)
    {
        $application = new Application('Slimer', Version::VERSION);

        foreach (self::builtInCommands($project) as $command) {
            $application->add($command);
        }

        foreach (self::customCommands($project) as $command) {
            $application->add($command);
        }

        return $application;
    }

    protected static function builtInCommands(Project $project)
    {
        return array(
            new AboutCommand($project),
            new InitCommand($project),
            new RouteListCommand($project),
            new ServeCommand($project),
            new MakeCommandCommand($project),
            new MakeControllerCommand($project),
            new MakeMiddlewareCommand($project),
            new MakeViewCommand($project),
        );
    }

    protected static function customCommands(Project $project)
    {
        $resolved = array();

        foreach ($project->configuration()->commands() as $definition) {
            $resolved[] = self::resolveCommand($definition, $project);
        }

        return $resolved;
    }

    protected static function resolveCommand($definition, Project $project)
    {
        if ($definition instanceof Command) {
            return $definition;
        }

        if (is_callable($definition)) {
            $command = call_user_func($definition, $project);

            if ($command instanceof Command) {
                return $command;
            }
        }

        if (is_string($definition) && class_exists($definition)) {
            $reflection = new ReflectionClass($definition);

            if (!$reflection->isSubclassOf('Symfony\Component\Console\Command\Command')) {
                throw new RuntimeException(sprintf('Configured command does not extend Symfony Command: %s', $definition));
            }

            $constructor = $reflection->getConstructor();

            if ($constructor === null || $constructor->getNumberOfRequiredParameters() === 0) {
                return $reflection->newInstance();
            }

            $parameters = $constructor->getParameters();

            if (count($parameters) === 1 && $parameters[0]->getClass() !== null) {
                if ($parameters[0]->getClass()->getName() === 'Twitnic\\Slimer\\Project') {
                    return $reflection->newInstance($project);
                }
            }
        }

        throw new RuntimeException('Unable to resolve a custom command from the Slimer configuration.');
    }
}
