<?php

namespace Twitnic\Slimer\Config;

use RuntimeException;
use Twitnic\Slimer\Filesystem;

class Configuration
{
    protected $path;
    protected $data;

    public function __construct($path, array $data)
    {
        $this->path = $path;
        $this->data = $data;
    }

    public static function fromDirectory($workingDirectory, Filesystem $filesystem)
    {
        $candidates = array_filter(array(
            getenv('SLIMER_CONFIG') ?: null,
            rtrim($workingDirectory, '/\\') . '/.slimer.php',
            rtrim($workingDirectory, '/\\') . '/config/slimer.php',
            rtrim($workingDirectory, '/\\') . '/app/config/slimer.php',
        ));

        foreach ($candidates as $candidate) {
            if (!$filesystem->exists($candidate)) {
                continue;
            }

            $data = require $candidate;

            if ($data === null || $data === 1) {
                $data = array();
            }

            if (!is_array($data)) {
                throw new RuntimeException(sprintf('The Slimer config file must return an array: %s', $candidate));
            }

            return new self($candidate, $data);
        }

        return new self(null, array());
    }

    public function exists()
    {
        return $this->path !== null;
    }

    public function path()
    {
        return $this->path;
    }

    public function all()
    {
        return $this->data;
    }

    public function bootstrap()
    {
        return array_key_exists('bootstrap', $this->data) ? $this->data['bootstrap'] : null;
    }

    public function commands()
    {
        $commands = array_key_exists('commands', $this->data) ? $this->data['commands'] : array();

        return is_array($commands) ? $commands : array();
    }

    public function generators()
    {
        $generators = array_key_exists('generators', $this->data) ? $this->data['generators'] : array();

        return is_array($generators) ? $generators : array();
    }

    public function generator($key, $default)
    {
        $generators = $this->generators();

        return array_key_exists($key, $generators) ? $generators[$key] : $default;
    }
}
