<?php

namespace Twitnic\Slimer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeControllerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('make:controller')
            ->setDescription('Generate a controller class.')
            ->addArgument('name', InputArgument::REQUIRED, 'The controller class name.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite an existing file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $this->resolveClassTarget(
            $input->getArgument('name'),
            'Controller',
            'controllers_path',
            'controller_namespace',
            'app/controllers',
            'App\\Controllers'
        );

        $path = $this->generator()->generate(
            $target['path'],
            'controller.stub',
            array(
                'DummyNamespace' => $this->namespaceLine($target['namespace']),
                'DummyClass' => $target['class'],
            ),
            $input->getOption('force')
        );

        $output->writeln('<info>Controller created:</info> ' . $path);

        return 0;
    }
}
