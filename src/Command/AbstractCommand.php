<?php

namespace Twitnic\Slimer\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Twitnic\Slimer\Generator\StubGenerator;
use Twitnic\Slimer\Project;

abstract class AbstractCommand extends Command
{
    protected $project;
    protected $stubGenerator;

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->stubGenerator = new StubGenerator(
            $project->filesystem(),
            dirname(dirname(__DIR__)) . '/resources/stubs'
        );

        parent::__construct();
    }

    protected function project()
    {
        return $this->project;
    }

    protected function generator()
    {
        return $this->stubGenerator;
    }

    protected function requireSlimApplication()
    {
        return $this->project->bootstrap();
    }

    protected function normalizeQualifiedClass($value)
    {
        $value = trim(str_replace('/', '\\', $value), '\\');
        $segments = array_filter(explode('\\', $value));
        $normalized = array();

        foreach ($segments as $segment) {
            $normalized[] = $this->studly($segment);
        }

        return implode('\\', $normalized);
    }

    protected function appendSuffix($className, $suffix)
    {
        $length = strlen($suffix);

        if ($length > 0 && strcasecmp(substr($className, -$length), $suffix) !== 0) {
            return $className . $suffix;
        }

        return $className;
    }

    protected function studly($value)
    {
        $parts = preg_split('/[-_\s]+/', $value);
        $studly = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $studly .= ucfirst(strtolower($part));
        }

        return $studly;
    }

    protected function namespaceLine($namespace)
    {
        $namespace = trim($namespace, '\\');

        return $namespace === '' ? '' : 'namespace ' . $namespace . ';';
    }

    protected function resolveClassTarget($name, $suffix, $pathKey, $namespaceKey, $defaultPath, $defaultNamespace)
    {
        $qualified = $this->normalizeQualifiedClass($name);

        if ($qualified === '') {
            throw new RuntimeException('A class name is required.');
        }

        $parts = explode('\\', $qualified);
        $className = array_pop($parts);
        $className = $this->appendSuffix($className, $suffix);

        $relativePath = $this->project()->configuration()->generator($pathKey, $defaultPath);
        $baseNamespace = trim($this->project()->configuration()->generator($namespaceKey, $defaultNamespace), '\\');

        $directory = $this->project()->resolvePath($relativePath);
        $namespace = $baseNamespace;

        if (!empty($parts)) {
            $directory .= '/' . implode('/', $parts);
            $namespace = trim($namespace . '\\' . implode('\\', $parts), '\\');
        }

        return array(
            'path' => $directory . '/' . $className . '.php',
            'class' => $className,
            'namespace' => $namespace,
        );
    }

    protected function exportPath($path)
    {
        return "'" . str_replace("'", "\\'", $path) . "'";
    }

    protected function commandSignatureFromClass($className)
    {
        $name = preg_replace('/Command$/', '', $className);
        $parts = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_map('strtolower', $parts);

        if (count($parts) <= 1) {
            return $parts[0] . ':handle';
        }

        $verb = array_shift($parts);

        return $verb . ':' . implode('-', $parts);
    }
}
