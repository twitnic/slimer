<?php

namespace Twitnic\Slimer;

use RuntimeException;
use Twitnic\Slimer\Config\Configuration;

class Project
{
    protected $workingDirectory;
    protected $filesystem;
    protected $configuration;
    protected $bootstrapAttempted = false;
    protected $application;

    public function __construct($workingDirectory, Filesystem $filesystem = null)
    {
        $this->workingDirectory = rtrim($workingDirectory ?: getcwd(), '/\\');
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function workingDirectory()
    {
        return $this->workingDirectory;
    }

    public function filesystem()
    {
        return $this->filesystem;
    }

    public function configuration()
    {
        if ($this->configuration === null) {
            $this->configuration = Configuration::fromDirectory($this->workingDirectory, $this->filesystem);
        }

        return $this->configuration;
    }

    public function hasConfiguration()
    {
        return $this->configuration()->exists();
    }

    public function resolvePath($path)
    {
        if ($path === null || $path === '') {
            return $this->workingDirectory;
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->workingDirectory . '/' . ltrim($path, '/\\');
    }

    public function bootstrap()
    {
        if ($this->bootstrapAttempted) {
            return $this->application;
        }

        $this->bootstrapAttempted = true;

        $bootstrap = $this->configuration()->bootstrap();

        if ($bootstrap !== null) {
            $this->application = $this->bootstrapFromDefinition($bootstrap);
        } else {
            foreach ($this->bootstrapCandidates() as $candidate) {
                if (!$this->filesystem->exists($candidate)) {
                    continue;
                }

                $application = $this->bootstrapFromFile($candidate);

                if ($application !== null) {
                    $this->application = $application;
                    break;
                }
            }
        }

        if ($this->application === null) {
            throw new RuntimeException(
                'Unable to bootstrap the Slim application. Provide a bootstrap entry in .slimer.php or expose a Slim\\Slim instance from your entry file.'
            );
        }

        return $this->application;
    }

    protected function bootstrapFromDefinition($bootstrap)
    {
        if (is_string($bootstrap)) {
            $path = $this->resolvePath($bootstrap);

            if (!$this->filesystem->exists($path)) {
                throw new RuntimeException(sprintf('Configured bootstrap file does not exist: %s', $path));
            }

            return $this->assertSlimApplication($this->bootstrapFromFile($path), $path);
        }

        if (is_callable($bootstrap)) {
            return $this->assertSlimApplication(
                $this->withBootstrapEnvironment(null, function () use ($bootstrap) {
                    return call_user_func($bootstrap, $this);
                }),
                'bootstrap callback'
            );
        }

        throw new RuntimeException('The "bootstrap" configuration value must be a file path or a callable.');
    }

    protected function bootstrapFromFile($path)
    {
        $returned = $this->withBootstrapEnvironment($path, function () use ($path) {
            $currentWorkingDirectory = getcwd();

            try {
                $directory = dirname($path);

                if ($currentWorkingDirectory !== false) {
                    chdir($directory);
                }

                return require basename($path);
            } finally {
                if ($currentWorkingDirectory !== false) {
                    chdir($currentWorkingDirectory);
                }
            }
        });

        if ($this->isSlimApplication($returned)) {
            return $returned;
        }

        if (class_exists('Slim\\Slim') && method_exists('Slim\\Slim', 'getInstance')) {
            $application = \Slim\Slim::getInstance();

            if ($this->isSlimApplication($application)) {
                return $application;
            }
        }

        return null;
    }

    protected function bootstrapCandidates()
    {
        return array(
            $this->resolvePath('public/index.php'),
            $this->resolvePath('index.php'),
            $this->resolvePath('app/bootstrap.php'),
            $this->resolvePath('bootstrap/app.php'),
        );
    }

    protected function assertSlimApplication($application, $context)
    {
        if ($this->isSlimApplication($application)) {
            return $application;
        }

        throw new RuntimeException(sprintf('Bootstrap target did not return a Slim\\Slim instance: %s', $context));
    }

    protected function isSlimApplication($value)
    {
        return class_exists('Slim\\Slim') && $value instanceof \Slim\Slim;
    }

    protected function isAbsolutePath($path)
    {
        return (bool) preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $path);
    }

    protected function withBootstrapEnvironment($path, $callback)
    {
        $state = $this->prepareCliServerEnvironment($path);

        try {
            return call_user_func($callback);
        } finally {
            $this->restoreCliServerEnvironment($state);
        }
    }

    protected function prepareCliServerEnvironment($path = null)
    {
        if (PHP_SAPI !== 'cli') {
            return null;
        }

        $defaults = array(
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_URI' => '/',
            'SERVER_NAME' => 'localhost',
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'QUERY_STRING' => '',
            'HTTPS' => 'off',
        );

        if (is_string($path) && $path !== '') {
            $scriptName = '/' . ltrim(str_replace('\\', '/', basename($path)), '/');

            $defaults['SCRIPT_FILENAME'] = $path;
            $defaults['SCRIPT_NAME'] = $scriptName;
            $defaults['PHP_SELF'] = $scriptName;
            $defaults['DOCUMENT_ROOT'] = dirname($path);
        }

        $added = array();

        foreach ($defaults as $key => $value) {
            if (array_key_exists($key, $_SERVER)) {
                continue;
            }

            $_SERVER[$key] = $value;
            $added[] = $key;
        }

        return array('added' => $added);
    }

    protected function restoreCliServerEnvironment($state)
    {
        if (!is_array($state) || !array_key_exists('added', $state)) {
            return;
        }

        foreach ($state['added'] as $key) {
            unset($_SERVER[$key]);
        }
    }
}
