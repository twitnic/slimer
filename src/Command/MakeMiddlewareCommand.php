<?php

namespace Twitnic\Slimer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMiddlewareCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('make:middleware')
            ->setDescription('Generate a Slim 2 middleware class.')
            ->addArgument('name', InputArgument::REQUIRED, 'The middleware class name.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite an existing file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $this->resolveClassTarget(
            $input->getArgument('name'),
            'Middleware',
            'middleware_path',
            'middleware_namespace',
            'app/middleware',
            'App\\Middleware'
        );

        $path = $this->generator()->generate(
            $target['path'],
            'middleware.stub',
            array(
                'DummyNamespace' => $this->namespaceLine($target['namespace']),
                'DummyClass' => $target['class'],
            ),
            $input->getOption('force')
        );

        $output->writeln('<info>Middleware created:</info> ' . $path);

        return 0;
    }
}
