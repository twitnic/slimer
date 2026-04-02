<?php

namespace Twitnic\Slimer\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Serve the application with the built-in PHP web server.')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Hostname to bind to.', '127.0.0.1')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Port to bind to.', 8080)
            ->addOption('docroot', null, InputOption::VALUE_REQUIRED, 'Document root to serve.')
            ->addOption('router', null, InputOption::VALUE_REQUIRED, 'Optional router script.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $docroot = $input->getOption('docroot') ?: $this->detectDocroot();
        $docroot = $this->project()->resolvePath($docroot);

        if (!$this->project()->filesystem()->isDirectory($docroot)) {
            throw new RuntimeException(sprintf('Document root does not exist: %s', $docroot));
        }

        $router = $input->getOption('router');
        $command = array(
            escapeshellarg(defined('PHP_BINARY') ? PHP_BINARY : 'php'),
            '-S',
            escapeshellarg($input->getOption('host') . ':' . $input->getOption('port')),
            '-t',
            escapeshellarg($docroot),
        );

        if ($router) {
            $command[] = escapeshellarg($this->project()->resolvePath($router));
        }

        $output->writeln('<info>Starting PHP development server.</info>');
        $output->writeln('Command: ' . implode(' ', $command));

        passthru(implode(' ', $command), $exitCode);

        return (int) $exitCode;
    }

    protected function detectDocroot()
    {
        $candidates = array('public', 'web', 'htdocs');

        foreach ($candidates as $candidate) {
            if ($this->project()->filesystem()->isDirectory($this->project()->resolvePath($candidate))) {
                return $candidate;
            }
        }

        return '.';
    }
}
