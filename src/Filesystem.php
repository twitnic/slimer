<?php

namespace Twitnic\Slimer;

use RuntimeException;

class Filesystem
{
    public function exists($path)
    {
        return file_exists($path);
    }

    public function isDirectory($path)
    {
        return is_dir($path);
    }

    public function makeDirectory($path, $mode, $recursive)
    {
        if ($this->isDirectory($path)) {
            return true;
        }

        return mkdir($path, $mode, $recursive);
    }

    public function read($path)
    {
        return file_get_contents($path);
    }

    public function put($path, $contents, $overwrite)
    {
        if ($this->exists($path) && !$overwrite) {
            throw new RuntimeException(sprintf('File already exists: %s', $path));
        }

        $directory = dirname($path);

        if (!$this->isDirectory($directory) && !$this->makeDirectory($directory, 0777, true)) {
            throw new RuntimeException(sprintf('Unable to create directory: %s', $directory));
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException(sprintf('Unable to write file: %s', $path));
        }

        return $path;
    }
}
