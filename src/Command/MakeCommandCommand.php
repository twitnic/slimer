<?php

namespace Twitnic\Slimer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCommandCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('make:command')
            ->setDescription('Generate a Symfony console command class.')
            ->addArgument('name', InputArgument::REQUIRED, 'The command class name.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite an existing file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $this->resolveClassTarget(
            $input->getArgument('name'),
            'Command',
            'commands_path',
            'command_namespace',
            'app/Console/Commands',
            'App\\Console\\Commands'
        );

        $path = $this->generator()->generate(
            $target['path'],
            'command.stub',
            array(
                'DummyNamespace' => $this->namespaceLine($target['namespace']),
                'DummyClass' => $target['class'],
                'DummySignature' => $this->commandSignatureFromClass($target['class']),
            ),
            $input->getOption('force')
        );

        $output->writeln('<info>Command created:</info> ' . $path);

        return 0;
    }
}
