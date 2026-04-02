<?php

namespace Twitnic\Slimer\Generator;

use RuntimeException;
use Twitnic\Slimer\Filesystem;

class StubGenerator
{
    protected $filesystem;
    protected $stubDirectory;

    public function __construct(Filesystem $filesystem, $stubDirectory)
    {
        $this->filesystem = $filesystem;
        $this->stubDirectory = rtrim($stubDirectory, '/\\');
    }

    public function generate($destination, $stubName, array $replacements, $overwrite)
    {
        $stubPath = $this->stubDirectory . '/' . $stubName;

        if (!$this->filesystem->exists($stubPath)) {
            throw new RuntimeException(sprintf('Stub not found: %s', $stubPath));
        }

        $contents = $this->filesystem->read($stubPath);
        $contents = strtr($contents, $replacements);

        return $this->filesystem->put($destination, $contents, $overwrite);
    }
}
